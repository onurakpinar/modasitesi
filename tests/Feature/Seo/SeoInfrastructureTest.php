<?php

namespace Tests\Feature\Seo;

use App\Enums\PageStatus;
use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostSlugRedirect;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Models\User;
use App\Support\PublicContent;
use App\Support\Seo\PostSlugRedirector;
use App\Support\Seo\SitemapGenerator;
use Database\Seeders\PageSeeder;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishablePostPayload;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class SeoInfrastructureTest extends TestCase
{
    use PublishablePostPayload;
    use PublishesStaticPages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
        URL::forceRootUrl('https://moda.test');
        URL::forceScheme('https');
    }

    public function test_sitemap_xml_gecerli_formatta(): void
    {
        Post::factory()->published()->create(['slug' => 'sitemap-xml-test']);

        $xml = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->getContent();

        $this->assertValidXml($xml);
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);

        $doc = new DOMDocument;
        $doc->loadXML($xml);
        $this->assertSame('urlset', $doc->documentElement->nodeName);
        $this->assertSame(
            'http://www.sitemaps.org/schemas/sitemap/0.9',
            $doc->documentElement->namespaceURI
        );
    }

    public function test_taslak_yazilar_sitemap_icinde_gorunmez(): void
    {
        Post::factory()->published()->create(['slug' => 'yayinda']);
        Post::factory()->create([
            'slug' => 'taslak-yazi',
            'status' => PostStatus::Draft,
        ]);
        Post::factory()->scheduled()->create(['slug' => 'planli-yazi']);

        $content = $this->get(route('sitemap'))->getContent();

        $this->assertStringContainsString('yayinda', $content);
        $this->assertStringNotContainsString('taslak-yazi', $content);
        $this->assertStringNotContainsString('planli-yazi', $content);
    }

    public function test_placeholder_iceren_yayinli_sabit_sayfalar_sitemap_icinde_gorunmez(): void
    {
        Page::query()->where('slug', 'hakkimizda')->update([
            'status' => PageStatus::Published,
            'body' => '<p>[SITE_ADI] hakkında bilgi.</p>',
        ]);

        $content = $this->get(route('sitemap'))->getContent();

        $this->assertStringNotContainsString('hakkimizda', $content);
    }

    public function test_silinen_yazilar_sitemap_icinde_gorunmez(): void
    {
        app(SitemapGenerator::class)->forget();

        $post = Post::factory()->published()->create(['slug' => 'silinecek-yazi']);
        $this->get(route('sitemap'))->assertSee('silinecek-yazi', false);

        $post->delete();

        $content = $this->get(route('sitemap'))->getContent();
        $this->assertStringNotContainsString('silinecek-yazi', $content);
    }

    public function test_rss_xml_gecerli_formatta(): void
    {
        Post::factory()->published()->create([
            'slug' => 'rss-xml-test',
            'originality_confirmed_at' => now(),
        ]);

        $xml = $this->get(route('rss'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->getContent();

        $this->assertValidXml($xml);
        $this->assertStringContainsString('<rss version="2.0"', $xml);
        $this->assertStringContainsString('<channel>', $xml);
    }

    public function test_robots_txt_dogru_content_type_ile_acilir(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_robots_txt_public_icerikleri_engellemez(): void
    {
        $content = $this->get(route('robots'))->getContent();

        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Disallow: /arama', $content);
        $this->assertStringNotContainsString('Disallow: /yazi/', $content);
        $this->assertStringNotContainsString('Disallow: /yazilar', $content);
        $this->assertStringNotContainsString('Disallow: /kategori/', $content);
        $this->assertStringNotContainsString('Disallow: /etiket/', $content);
        $this->assertStringNotContainsString('Disallow: /yazar/', $content);
        $this->assertStringContainsString('Sitemap: https://moda.test/sitemap.xml', $content);
    }

    public function test_arama_sayfasi_noindex_follow_olur(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, follow"', false);

        $this->get(route('search', ['q' => 'moda']))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, follow"', false);
    }

    public function test_preview_sayfasi_noindex_nofollow_olur(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $post = Post::factory()->create();

        $previewUrl = URL::temporarySignedRoute('admin.posts.preview', now()->addHour(), ['post' => $post->id]);

        $this->actingAs($admin)
            ->get($previewUrl)
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_admin_giris_sayfasi_noindex_nofollow_olur(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_admin_panel_noindex_nofollow_olur(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_bos_yazar_profili_noindex_olur(): void
    {
        $author = Author::factory()->create(['slug' => 'bos-yazar']);

        $this->get(route('authors.show', $author->slug))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, follow"', false);
    }

    public function test_az_icerikli_etiket_sayfasi_noindex_olur(): void
    {
        $tag = Tag::factory()->create(['slug' => 'tek-etiket']);
        $posts = Post::factory()->published()->count(2)->create();
        $tag->posts()->attach($posts->pluck('id'));

        $this->get(route('tags.show', $tag->slug))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, follow"', false);
    }

    public function test_etiket_sayfasi_uc_yazida_index_olur(): void
    {
        $tag = Tag::factory()->create(['slug' => 'cok-etiket']);
        $posts = Post::factory()->published()->count(3)->create();
        $tag->posts()->attach($posts->pluck('id'));

        $this->get(route('tags.show', $tag->slug))
            ->assertOk()
            ->assertSee('name="robots" content="index, follow"', false);
    }

    public function test_canonical_url_yazida_dogru_uretilir(): void
    {
        $post = Post::factory()->published()->create([
            'slug' => 'kanonik-yazi',
            'canonical_url' => 'https://moda.test/ozel-kanonik',
        ]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('rel="canonical" href="https://moda.test/ozel-kanonik"', false);
    }

    public function test_json_ld_syntax_gecerli_ve_sahte_veri_icermez(): void
    {
        $author = Author::factory()->create(['name' => 'Gerçek Yazar']);
        $post = Post::factory()->published()->create([
            'author_id' => $author->id,
            'slug' => 'json-ld-test',
            'title' => 'JSON-LD Test Yazısı',
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();
        $schemas = $this->extractJsonLdBlocks($html);

        $this->assertGreaterThanOrEqual(2, count($schemas));

        foreach ($schemas as $schema) {
            $this->assertIsArray($schema);
            $encoded = json_encode($schema);
            $this->assertNotFalse($encoded);
            $this->assertStringNotContainsString('streetAddress', $encoded);
            $this->assertStringNotContainsString('PostalAddress', $encoded);
            $this->assertStringNotContainsString('sameAs', $encoded);
            $this->assertStringNotContainsString('example.com', $encoded);
            $this->assertStringNotContainsString('Lorem ipsum', $encoded);
        }

        $article = collect($schemas)->first(fn ($s) => ($s['@type'] ?? null) === 'Article');
        $this->assertNotNull($article);
        $this->assertSame('Gerçek Yazar', $article['author']['name'] ?? null);
        $this->assertSame('JSON-LD Test Yazısı', $article['headline'] ?? null);
    }

    public function test_ana_sayfa_json_ld_sahte_sirket_verisi_icermez(): void
    {
        $html = $this->get(route('home'))->getContent();
        $schemas = $this->extractJsonLdBlocks($html);

        foreach ($schemas as $schema) {
            $encoded = json_encode($schema);
            $this->assertStringNotContainsString('streetAddress', $encoded);
            $this->assertStringNotContainsString('sameAs', $encoded);
            $this->assertStringNotContainsString('example.com', $encoded);
        }

        $organization = collect($schemas)->first(fn ($s) => ($s['@type'] ?? null) === 'Organization');
        $this->assertNotNull($organization);
        $this->assertArrayHasKey('name', $organization);
        $this->assertArrayHasKey('url', $organization);
        $this->assertArrayNotHasKey('address', $organization);
    }

    public function test_slug_degistiginde_301_yonlendirme_calisir(): void
    {
        $post = Post::factory()->published()->create([
            'slug' => 'eski-slug',
            'title' => 'Eski Başlık',
        ]);

        $post->update(['slug' => 'yeni-slug']);
        app(PostSlugRedirector::class)->record($post->fresh(), 'eski-slug');

        $this->get(route('posts.show', 'eski-slug'))
            ->assertRedirect(route('posts.show', 'yeni-slug'))
            ->assertStatus(301);

        $this->get(route('posts.show', 'yeni-slug'))
            ->assertOk()
            ->assertSee('Eski Başlık', false);
    }

    public function test_redirect_dongusu_olusturmaz(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'slug-b']);
        PostSlugRedirect::query()->create([
            'post_id' => $post->id,
            'old_slug' => 'slug-a',
            'created_at' => now(),
        ]);

        $response = $this->get(route('posts.show', 'slug-a'));
        $response->assertRedirect(route('posts.show', 'slug-b'));
        $response->assertStatus(301);

        $this->get(route('posts.show', 'slug-b'))
            ->assertOk()
            ->assertStatus(200);

        $post->update(['slug' => 'slug-a']);
        app(PostSlugRedirector::class)->record($post->fresh(), 'slug-b');

        $this->assertDatabaseMissing('post_slug_redirects', ['old_slug' => 'slug-a']);

        $this->get(route('posts.show', 'slug-a'))->assertOk();
        $this->get(route('posts.show', 'slug-b'))
            ->assertRedirect(route('posts.show', 'slug-a'))
            ->assertStatus(301);
    }

    public function test_sitemap_cache_yazi_olusturulunca_guncellenir(): void
    {
        app(SitemapGenerator::class)->forget();

        $this->get(route('sitemap'))->assertOk();
        $this->assertTrue(Cache::has(SitemapGenerator::CACHE_KEY));

        Post::factory()->published()->create(['slug' => 'yeni-sitemap-yazi']);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('yeni-sitemap-yazi', false);
    }

    public function test_sitemap_cache_yazi_guncellenince_temizlenir(): void
    {
        app(SitemapGenerator::class)->forget();

        $post = Post::factory()->published()->create(['slug' => 'eski-sitemap-slug']);
        $this->get(route('sitemap'))->assertSee('eski-sitemap-slug', false);

        $post->update(['slug' => 'yeni-sitemap-slug']);

        $content = $this->get(route('sitemap'))->getContent();
        $this->assertStringNotContainsString('eski-sitemap-slug', $content);
        $this->assertStringContainsString('yeni-sitemap-slug', $content);
    }

    public function test_sitemap_cache_yazi_silinince_temizlenir(): void
    {
        app(SitemapGenerator::class)->forget();

        $post = Post::factory()->published()->create(['slug' => 'cache-silme-test']);
        $this->get(route('sitemap'))->assertSee('cache-silme-test', false);

        $post->delete();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertDontSee('cache-silme-test', false);
    }

    public function test_sitemap_yalnizca_public_icerikleri_listeler(): void
    {
        $this->publishStaticPagesForTests();

        $category = Category::factory()->create(['slug' => 'moda']);
        $author = Author::factory()->create(['slug' => 'yazar', 'is_active' => true]);
        $published = Post::factory()->published()->create([
            'slug' => 'yayinda-yazi',
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $content = $this->get(route('sitemap'))->getContent();

        $this->assertStringContainsString(route('posts.show', $published->slug), $content);
        $this->assertStringContainsString(route('categories.show', $category->slug), $content);
        $this->assertStringContainsString(route('authors.show', $author->slug), $content);
        $this->assertStringContainsString(route('pages.about'), $content);
        $this->assertStringNotContainsString('/arama', $content);
        $this->assertStringNotContainsString('/admin', $content);
    }

    public function test_rss_son_yayinlanan_ozgun_yazilari_listeler(): void
    {
        Post::factory()->published()->count(5)->create([
            'originality_confirmed_at' => now(),
        ]);
        Post::factory()->published()->count(2)->create([
            'originality_confirmed_at' => null,
        ]);

        $xml = $this->get(route('rss'))->getContent();
        $this->assertSame(5, substr_count($xml, '<item>'));
    }

    public function test_googlebot_public_yazilara_erisebilir(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'googlebot-yazi']);

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('name="robots" content="index, follow"', false);

        $robots = $this->withHeaders(['User-Agent' => 'Googlebot'])
            ->get(route('robots'))
            ->getContent();

        $this->assertStringContainsString('Allow: /yazi/', $robots);
        $this->assertStringContainsString('Disallow: /admin', $robots);
    }

    public function test_404_sayfasi_noindex_olur(): void
    {
        $this->get('/olmayan-sayfa')
            ->assertNotFound()
            ->assertSee('name="robots" content="noindex"', false);
    }

    public function test_turkce_slug_donusturme_calisir(): void
    {
        $this->assertSame('istanbul-moda-gunu', Post::generateUniqueSlug('İstanbul Moda Günü'));
    }

    public function test_sabit_sayfalar_seo_meta_icerir(): void
    {
        $this->publishStaticPagesForTests();

        foreach (PublicContent::staticPageRoutes() as $routeName => $slug) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('rel="canonical"', false)
                ->assertSee('property="og:title"', false);
        }
    }

    public function test_yazilar_listesi_seo_meta_icerir(): void
    {
        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('<title>Yazılar — '.config('site.name').'</title>', false)
            ->assertSee('rel="canonical"', false);
    }

    public function test_ana_sayfa_seo_meta_ve_json_ld_icerir(): void
    {
        SiteSetting::set('default_meta_title', 'Moda Yayını');
        SiteSetting::set('default_meta_description', 'Türkiye moda içerikleri');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>Moda Yayını</title>', false)
            ->assertSee('rel="canonical" href="https://moda.test"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('name="robots" content="index, follow"', false);
    }

    private function assertValidXml(string $xml): void
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $loaded = $document->loadXML($xml);
        $error = libxml_get_last_error();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $this->assertTrue($loaded, $error ? $error->message : 'XML parse edilemedi.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractJsonLdBlocks(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $schemas = [];

        foreach ($matches[1] as $json) {
            $decoded = json_decode(html_entity_decode($json), true);
            $this->assertIsArray($decoded, 'JSON-LD geçersiz: '.$json);
            $schemas[] = $decoded;
        }

        return $schemas;
    }
}
