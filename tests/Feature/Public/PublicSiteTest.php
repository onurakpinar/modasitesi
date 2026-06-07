<?php

namespace Tests\Feature\Public;

use App\Enums\PageStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Support\PublicContent;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use PublishesStaticPages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_ana_sayfa_yuklenir(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(config('site.name'), false);
    }

    public function test_ana_sayfada_yayinlanmis_yazilar_gorunur(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'Sonbahar Trendleri',
            'is_featured' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sonbahar Trendleri', false)
            ->assertSee('Öne çıkan', false);
    }

    public function test_yazilar_listesi_sayfalanir(): void
    {
        Post::factory()->published()->count(13)->create();

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('Yazılar', false);

        $this->get(route('posts.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('rel="prev"', false);
    }

    public function test_yazilar_kategori_filtresi_calisir(): void
    {
        $category = Category::factory()->create(['name' => 'Aksesuar', 'slug' => 'aksesuar']);
        $other = Category::factory()->create();

        Post::factory()->published()->create(['category_id' => $category->id, 'title' => 'Şapka Rehberi']);
        Post::factory()->published()->create(['category_id' => $other->id, 'title' => 'Gizli Kategori Yazısı']);

        $this->get(route('posts.index', ['kategori' => 'aksesuar']))
            ->assertOk()
            ->assertSee('Şapka Rehberi', false)
            ->assertDontSee('Gizli Kategori Yazısı', false);
    }

    public function test_yazi_detay_sayfasi_icerikleri_gosterir(): void
    {
        $author = Author::factory()->create(['name' => 'Ayşe Yılmaz', 'slug' => 'ayse-yilmaz']);
        $category = Category::factory()->create(['name' => 'Kadın Modası', 'slug' => 'kadin-modasi']);
        $tag = Tag::factory()->create(['name' => 'Trend', 'slug' => 'trend']);

        $post = Post::factory()->published()->create([
            'title' => 'Minimal Gardırop',
            'slug' => 'minimal-gardirop',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'body' => 'Sade parçalarla şık kombinler.',
        ]);
        $post->tags()->attach($tag);

        Post::factory()->published()->create([
            'category_id' => $category->id,
            'title' => 'İlgili Yazı',
            'slug' => 'ilgili-yazi',
        ]);

        $this->get(route('posts.show', 'minimal-gardirop'))
            ->assertOk()
            ->assertSee('Minimal Gardırop', false)
            ->assertSee('Kadın Modası', false)
            ->assertSee('Ayşe Yılmaz', false)
            ->assertSee('Trend', false)
            ->assertSee('Sade parçalarla şık kombinler.', false)
            ->assertSee('İlgili Yazılar', false);
    }

    public function test_kategori_sayfasi_yayinlanmis_yazilari_listeler(): void
    {
        $category = Category::factory()->create([
            'name' => 'Erkek Modası',
            'slug' => 'erkek-modasi',
            'description' => 'Erkek stiline dair yazılar.',
        ]);

        Post::factory()->published()->create([
            'category_id' => $category->id,
            'title' => 'Ceket Seçimi',
        ]);

        $this->get(route('categories.show', 'erkek-modasi'))
            ->assertOk()
            ->assertSee('Erkek Modası', false)
            ->assertSee('Erkek stiline dair yazılar.', false)
            ->assertSee('Ceket Seçimi', false);
    }

    public function test_etiket_sayfasi_calisir(): void
    {
        $tag = Tag::factory()->create(['name' => 'Sürdürülebilir', 'slug' => 'surdurulebilir']);
        $post = Post::factory()->published()->create(['title' => 'Yeşil Moda']);
        $post->tags()->attach($tag);

        $this->get(route('tags.show', 'surdurulebilir'))
            ->assertOk()
            ->assertSee('Sürdürülebilir', false)
            ->assertSee('Yeşil Moda', false);
    }

    public function test_yazar_sayfasi_calisir(): void
    {
        $author = Author::factory()->create([
            'name' => 'Mehmet Kaya',
            'slug' => 'mehmet-kaya',
            'short_bio' => 'Moda editörü ve stil danışmanı.',
        ]);

        Post::factory()->published()->create([
            'author_id' => $author->id,
            'title' => 'Yazarın Yazısı',
        ]);

        $this->get(route('authors.show', 'mehmet-kaya'))
            ->assertOk()
            ->assertSee('Mehmet Kaya', false)
            ->assertSee('Moda editörü ve stil danışmanı.', false)
            ->assertSee('Yazarın Yazısı', false);
    }

    public function test_arama_sonuclari_ve_bos_sonuc(): void
    {
        Post::factory()->published()->create(['title' => 'Kış Montları Rehberi']);

        $this->get(route('search', ['q' => 'mont']))
            ->assertOk()
            ->assertSee('Kış Montları Rehberi', false)
            ->assertSee('mont', false);

        $this->get(route('search', ['q' => '<script>alert(1)</script>']))
            ->assertOk()
            ->assertDontSee('<script>', false);

        $this->get(route('search', ['q' => 'bulunamayacakkelime']))
            ->assertOk()
            ->assertSee('sonuç bulunamadı', false);
    }

    public function test_sabit_sayfalar_yuklenir(): void
    {
        $this->publishStaticPagesForTests();

        foreach (PublicContent::staticPageRoutes() as $routeName => $slug) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee(PublicContent::staticPageLabels()[$slug], false);
        }
    }

    public function test_taslak_ve_planlanmis_yazilar_publicte_gorunmez(): void
    {
        Post::factory()->create([
            'title' => 'Taslak Yazı',
            'slug' => 'taslak-yazi',
            'status' => \App\Enums\PostStatus::Draft,
        ]);

        Post::factory()->scheduled()->create([
            'title' => 'Gelecek Yazı',
            'slug' => 'gelecek-yazi',
        ]);

        $this->get(route('posts.show', 'taslak-yazi'))->assertNotFound();
        $this->get(route('posts.show', 'gelecek-yazi'))->assertNotFound();
        $this->get(route('posts.index'))->assertDontSee('Taslak Yazı', false)->assertDontSee('Gelecek Yazı', false);
    }

    public function test_bos_kategori_navigasyonda_gorunmez(): void
    {
        $empty = Category::factory()->create(['name' => 'Boş Kategori', 'slug' => 'bos-kategori']);
        $filled = Category::factory()->create(['name' => 'Dolu Kategori', 'slug' => 'dolu-kategori']);
        Post::factory()->published()->create(['category_id' => $filled->id]);

        $response = $this->get(route('home'));

        $response->assertDontSee('Boş Kategori', false);
        $response->assertSee('Dolu Kategori', false);

        $this->get(route('categories.show', $empty->slug))->assertNotFound();
    }

    public function test_yayinlanmamis_sabit_sayfa_404_doner(): void
    {
        Page::query()->where('slug', 'hakkimizda')->update(['status' => PageStatus::Draft]);

        $this->get(route('pages.about'))->assertNotFound();
    }
}
