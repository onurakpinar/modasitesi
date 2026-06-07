<?php

namespace Tests\Feature\Public;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Support\PublicContent;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class PublicSiteRegressionTest extends TestCase
{
    use PublishesStaticPages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
        $this->publishStaticPagesForTests();
    }

    public function test_taslak_yazi_public_tarafta_gorunmez(): void
    {
        Post::factory()->create([
            'title' => 'Gizli Taslak',
            'slug' => 'gizli-taslak',
            'status' => PostStatus::Draft,
        ]);

        $this->get(route('posts.show', 'gizli-taslak'))->assertNotFound();
        $this->get(route('home'))->assertDontSee('Gizli Taslak', false);
        $this->get(route('posts.index'))->assertDontSee('Gizli Taslak', false);
    }

    public function test_gelecek_tarihli_yayinlanmis_yazi_erkenden_gorunmez(): void
    {
        Post::factory()->create([
            'title' => 'Erken Yayın',
            'slug' => 'erken-yayin',
            'status' => PostStatus::Published,
            'published_at' => now()->addWeek(),
        ]);

        $this->get(route('posts.show', 'erken-yayin'))->assertNotFound();
        $this->get(route('posts.index'))->assertDontSee('Erken Yayın', false);
        $this->get(route('home'))->assertDontSee('Erken Yayın', false);
    }

    public function test_zamanlanmis_yazi_publicte_gorunmez(): void
    {
        Post::factory()->scheduled()->create([
            'title' => 'Zamanlanmış Yazı',
            'slug' => 'zamanlanmis-yazi',
        ]);

        $this->get(route('posts.show', 'zamanlanmis-yazi'))->assertNotFound();
        $this->get(route('posts.index'))->assertDontSee('Zamanlanmış Yazı', false);
    }

    public function test_arsivlenmis_yazi_publicte_gorunmez(): void
    {
        Post::factory()->create([
            'title' => 'Arşiv Yazısı',
            'slug' => 'arsiv-yazisi',
            'status' => PostStatus::Archived,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('posts.show', 'arsiv-yazisi'))->assertNotFound();
    }

    public function test_soft_delete_edilmis_yazi_erisilemez(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'Silinen Yazı',
            'slug' => 'silinen-yazi',
        ]);

        $post->delete();

        $this->get(route('posts.show', 'silinen-yazi'))->assertNotFound();
        $this->get(route('posts.index'))->assertDontSee('Silinen Yazı', false);
    }

    public function test_slug_bulunamadiginda_404_doner(): void
    {
        $this->get(route('posts.show', 'olmayan-yazi'))->assertNotFound();
        $this->get(route('categories.show', 'olmayan-kategori'))->assertNotFound();
        $this->get(route('tags.show', 'olmayan-etiket'))->assertNotFound();
        $this->get(route('authors.show', 'olmayan-yazar'))->assertNotFound();
    }

    public function test_bos_kategori_menude_gorunmez(): void
    {
        Category::factory()->create(['name' => 'Boş Menü Kategori', 'slug' => 'bos-menu']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Boş Menü Kategori', false);
    }

    public function test_arama_sorgusunda_xss_olusturmaz(): void
    {
        $payloads = [
            '<script>alert("xss")</script>',
            '<img src=x onerror=alert(1)>',
            '"><svg/onload=alert(1)>',
        ];

        foreach ($payloads as $payload) {
            $response = $this->get(route('search', ['q' => $payload]));

            $response->assertOk();
            $content = $response->getContent();

            $this->assertStringNotContainsString($payload, $content);
            $this->assertStringNotContainsString('onerror=alert', $content);
            $this->assertStringNotContainsString('onload=alert', $content);
            $this->assertStringNotContainsString('<script>alert', $content);
        }
    }

    public function test_turkce_slug_uretimi_dogru_calisir(): void
    {
        $this->assertSame('kadin-modasi', Post::generateUniqueSlug('Kadın Modası'));
        $this->assertSame('sapka-rehberi', Category::generateUniqueSlug('Şapka Rehberi'));
        $this->assertSame('surdurulebilir-moda', Tag::generateUniqueSlug('Sürdürülebilir Moda'));
    }

    public function test_mobil_menu_yapisi_mevcut(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('x-ref="menuButton"', false)
            ->assertSee('x-ref="mobileNav"', false)
            ->assertSee('id="site-navigation-mobile"', false)
            ->assertSee('id="site-navigation-desktop"', false)
            ->assertSee('aria-controls="site-navigation-mobile"', false)
            ->assertSee('lg:hidden', false);
    }

    public function test_kapak_gorseliz_yazi_fallback_ile_goruntulenir(): void
    {
        Post::factory()->published()->create([
            'title' => 'Görselsiz Yazı',
            'slug' => 'gorselsiz-yazi',
            'cover_image' => null,
        ]);

        $this->get(route('posts.show', 'gorselsiz-yazi'))
            ->assertOk()
            ->assertSee('Görselsiz Yazı', false)
            ->assertSee('role="img"', false)
            ->assertSee('aspect-[16/9]', false);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('aspect-[4/3]', false)
            ->assertSee('role="img"', false);
    }

    public function test_ilgili_yazilar_yalnizca_ayni_kategoriden_gelir(): void
    {
        $categoryA = Category::factory()->create(['slug' => 'kategori-a']);
        $categoryB = Category::factory()->create(['slug' => 'kategori-b']);

        $main = Post::factory()->published()->create([
            'title' => 'Ana Yazı',
            'slug' => 'ana-yazi',
            'category_id' => $categoryA->id,
        ]);

        Post::factory()->published()->create([
            'title' => 'A Kategorisi İlgili',
            'slug' => 'a-ilgili',
            'category_id' => $categoryA->id,
        ]);

        Post::factory()->published()->create([
            'title' => 'B Kategorisi Olmamalı',
            'slug' => 'b-olmamali',
            'category_id' => $categoryB->id,
        ]);

        $this->get(route('posts.show', $main->slug))
            ->assertOk()
            ->assertSee('A Kategorisi İlgili', false)
            ->assertDontSee('B Kategorisi Olmamalı', false);
    }

    public function test_yazilar_listesinde_n_plus_one_olusturmaz(): void
    {
        Post::factory()->published()->count(3)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('posts.index'))->assertOk();
        $fewPostsQueryCount = count(DB::getQueryLog());

        Post::factory()->published()->count(10)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('posts.index'))->assertOk();
        $manyPostsQueryCount = count(DB::getQueryLog());

        $this->assertLessThanOrEqual(
            $fewPostsQueryCount + 3,
            $manyPostsQueryCount,
            'Yazı sayısı arttıkça sorgu sayısı orantısız yükseldi (N+1 şüphesi).'
        );
    }

    public function test_ana_sayfada_yazilar_icin_n_plus_one_olusturmaz(): void
    {
        Post::factory()->published()->count(2)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('home'))->assertOk();
        $fewPostsQueryCount = count(DB::getQueryLog());

        Post::factory()->published()->featured()->count(6)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('home'))->assertOk();
        $manyPostsQueryCount = count(DB::getQueryLog());

        $this->assertLessThanOrEqual(
            $fewPostsQueryCount + 5,
            $manyPostsQueryCount,
            'Ana sayfada yazı sayısı arttıkça sorgu sayısı orantısız yükseldi (N+1 şüphesi).'
        );
    }

    public function test_public_view_dosyalari_responsive_siniflar_icerir(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'responsive-test']);

        $pages = [
            route('home'),
            route('posts.index'),
            route('posts.show', $post->slug),
            route('search'),
        ];

        foreach ($pages as $url) {
            $content = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('sm:', $content, "Responsive sınıf bulunamadı: {$url}");
            $this->assertStringContainsString('max-w-', $content, "Genişlik sınırı bulunamadı: {$url}");
        }
    }

    public function test_footer_baglantilari_dogru_url_lere_gider(): void
    {
        $category = Category::factory()->create(['name' => 'Footer Kategori', 'slug' => 'footer-kat']);
        Post::factory()->published()->create(['category_id' => $category->id]);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee(route('categories.show', 'footer-kat'), false);
        $response->assertSee(route('posts.index'), false);
        $response->assertSee(route('search'), false);

        foreach (PublicContent::footerStaticPageSlugs() as $slug) {
            $routeName = PublicContent::staticPageRouteName($slug);
            $response->assertSee(route($routeName), false);
        }
    }

    public function test_eksik_sabit_sayfa_500_degil_404_doner(): void
    {
        Page::query()->where('slug', 'hakkimizda')->delete();

        $this->get(route('pages.about'))
            ->assertNotFound()
            ->assertDontSee('Server Error', false);
    }

    public function test_pasif_yazar_publicte_gorunmez(): void
    {
        $author = Author::factory()->inactive()->create(['slug' => 'pasif-yazar']);
        Post::factory()->published()->create(['author_id' => $author->id, 'title' => 'Pasif Yazar Yazısı']);

        $this->get(route('authors.show', 'pasif-yazar'))->assertNotFound();
    }

    public function test_tum_public_route_lar_erisilebilir(): void
    {
        $category = Category::factory()->create(['slug' => 'route-kat']);
        $tag = Tag::factory()->create(['slug' => 'route-etiket']);
        $author = Author::factory()->create(['slug' => 'route-yazar']);
        $post = Post::factory()->published()->create([
            'slug' => 'route-yazi',
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);
        $post->tags()->attach($tag);

        $routes = [
            route('home'),
            route('posts.index'),
            route('posts.show', 'route-yazi'),
            route('categories.show', 'route-kat'),
            route('tags.show', 'route-etiket'),
            route('authors.show', 'route-yazar'),
            route('search'),
            route('search', ['q' => 'route']),
        ];

        foreach (PublicContent::staticPageRoutes() as $routeName => $slug) {
            $routes[] = route($routeName);
        }

        foreach ($routes as $url) {
            $this->get($url)->assertOk();
        }
    }
}
