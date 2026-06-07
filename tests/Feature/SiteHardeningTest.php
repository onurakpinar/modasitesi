<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\User;
use App\Support\Ads\AdSettings;
use App\Support\HomePageCache;
use App\Support\SecureImageUploader;
use Illuminate\Http\UploadedFile;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class SiteHardeningTest extends TestCase
{
    use PublishesStaticPages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
        $this->publishStaticPagesForTests();
    }

    public function test_guvenlik_basliklari_public_route_larda_eklenir(): void
    {
        $post = Post::factory()->published()->create();

        foreach ([route('home'), route('posts.index'), route('posts.show', $post->slug), route('search'), route('sitemap'), route('robots')] as $url) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
            $csp = (string) $response->headers->get('Content-Security-Policy');
            $this->assertStringContainsString('default-src', $csp, "CSP eksik: {$url}");
            $this->assertStringNotContainsString('unsafe-eval', $csp, "CSP unsafe-eval içermemeli: {$url}");
        }
    }

    public function test_health_endpoint_ortam_bilgisi_sizdirmaz(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('checks.database', 'ok')
            ->assertJsonMissing(['environment' => config('app.env')]);
    }

    public function test_ana_sayfa_icerigi_cache_lenir(): void
    {
        Post::factory()->published()->count(3)->create();

        $this->get(route('home'))->assertOk();

        $this->assertTrue(Cache::has(HomePageCache::CACHE_KEY));
    }

    public function test_yazi_guncellenince_ana_sayfa_cache_temizlenir(): void
    {
        $post = Post::factory()->published()->create();

        $this->get(route('home'))->assertOk();
        $this->assertTrue(Cache::has(HomePageCache::CACHE_KEY));

        $post->update(['title' => 'Güncellenmiş başlık']);

        $this->assertFalse(Cache::has(HomePageCache::CACHE_KEY));
    }

    public function test_iletisim_formu_basariyla_gonderilir(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse@ornek.com',
            'message' => 'Merhaba, iş birliği hakkında bilgi almak istiyorum.',
            'privacy_acknowledged' => '1',
        ]);

        $response->assertRedirect(route('pages.contact'));
        $response->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ayse@ornek.com',
            'subject' => 'İletişim formu',
        ]);
    }

    public function test_iletisim_formu_csrf_korumasi_aktif(): void
    {
        $route = app('router')->getRoutes()->getByName('contact.store');

        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains(
            PreventRequestForgery::class,
            (new Middleware)->getMiddlewareGroups()['web']
        );

        $this->get(route('pages.contact'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_iletisim_formu_xss_olusturmaz(): void
    {
        $payload = '<script>alert("xss")</script>';

        $this->post(route('contact.store'), [
            'name' => $payload,
            'email' => 'test@ornek.com',
            'subject' => $payload,
            'message' => $payload,
            'privacy_acknowledged' => '1',
        ])->assertRedirect(route('pages.contact'));

        $message = ContactMessage::query()->firstOrFail();
        $this->assertStringNotContainsString('<script>', $message->message);

        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertDontSee($payload, false);
    }

    public function test_iletisim_formu_honeypot_doluysa_kayit_olusturmaz(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@spam.com',
            'subject' => 'Spam',
            'message' => 'Spam mesajı',
            'privacy_acknowledged' => '1',
            'company_website' => 'https://spam.test',
        ])
            ->assertRedirect(route('pages.contact'))
            ->assertSessionHas('contact_success');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_iletisim_formu_dogrulama_hatalari_gosterir(): void
    {
        $this->from(route('pages.contact'))
            ->post(route('contact.store'), [])
            ->assertRedirect(route('pages.contact'))
            ->assertSessionHasErrors(['name', 'email', 'message', 'privacy_acknowledged']);
    }

    public function test_iletisim_formu_rate_limit_uygular(): void
    {
        RateLimiter::clear('contact-form:127.0.0.1');

        $payload = [
            'name' => 'Test Kullanıcı',
            'email' => 'test@ornek.com',
            'message' => 'Mesaj içeriği',
            'privacy_acknowledged' => '1',
        ];

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('contact.store'), $payload)->assertRedirect();
        }

        $this->post(route('contact.store'), $payload)->assertStatus(429);
    }

    public function test_arama_rate_limit_uygular(): void
    {
        RateLimiter::clear('search:127.0.0.1');

        for ($i = 0; $i < 30; $i++) {
            $this->get(route('search', ['q' => 'moda'.$i]))->assertOk();
        }

        $this->get(route('search', ['q' => 'limit']))->assertStatus(429);
    }

    public function test_iletisim_sayfasinda_form_ve_erisilebilirlik_ogeleri_bulunur(): void
    {
        $response = $this->get(route('pages.contact'))->assertOk();

        $response->assertSee('İçeriğe atla', false);
        $response->assertSee('id="contact_name"', false);
        $response->assertSee('id="contact_email"', false);
        $response->assertSee('Gizlilik politikasını', false);
        $response->assertSee('aria-expanded', false);
        $response->assertSee('aria-controls="site-navigation-mobile"', false);
    }

    public function test_yazi_detayinda_lcp_gorseli_lazy_yuklenmez(): void
    {
        Storage::fake('public');

        $jpeg = $this->createTinyJpeg();
        Storage::disk('public')->put('posts/detail.webp', $jpeg);
        Storage::disk('public')->put('posts/detail.jpg', $jpeg);

        $post = Post::factory()->published()->create([
            'slug' => 'lcp-test-yazi',
            'cover_image' => 'posts/detail.webp',
            'cover_image_fallback' => 'posts/detail.jpg',
            'cover_image_width' => 1200,
            'cover_image_height' => 675,
        ]);

        $response = $this->get(route('posts.show', $post->slug))->assertOk();

        $response->assertSee('loading="eager"', false);
        $response->assertSee('fetchpriority="high"', false);
        $response->assertSee('width="1200"', false);
        $response->assertSee('height="675"', false);
        $response->assertDontSee('loading="lazy"', false);
    }

    public function test_kapak_gorselinde_boyut_ve_lazy_loading_ozellikleri(): void
    {
        Storage::fake('public');

        $jpeg = $this->createTinyJpeg();
        Storage::disk('public')->put('posts/test.webp', $jpeg);
        Storage::disk('public')->put('posts/test.jpg', $jpeg);

        Post::factory()->published()->featured()->create([
            'cover_image' => 'posts/test.webp',
            'cover_image_fallback' => 'posts/test.jpg',
            'cover_image_width' => 1600,
            'cover_image_height' => 1000,
        ]);

        Post::factory()->published()->create([
            'cover_image' => 'posts/test.webp',
            'cover_image_fallback' => 'posts/test.jpg',
            'cover_image_width' => 800,
            'cover_image_height' => 600,
        ]);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('width="1600"', false);
        $response->assertSee('height="1000"', false);
        $response->assertSee('fetchpriority="high"', false);
        $response->assertSee('loading="lazy"', false);
        $response->assertSee('type="image/webp"', false);
    }

    public function test_admin_sidebar_okunmamis_mesaj_sayacini_gosterir(): void
    {
        ContactMessage::query()->create([
            'name' => 'Ziyaretçi',
            'email' => 'ziyaretci@ornek.com',
            'subject' => 'Soru',
            'message' => 'Merhaba',
            'ip_address' => '127.0.0.1',
        ]);

        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('aria-label="1 okunmamış mesaj"', false);
    }

    public function test_kategori_ve_arama_sayfalarinda_n_plus_one_olusturmaz(): void
    {
        Post::factory()->published()->count(3)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('posts.index'))->assertOk();
        $baseline = count(DB::getQueryLog());

        Post::factory()->published()->count(8)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('search', ['q' => 'moda']))->assertOk();
        $searchQueries = count(DB::getQueryLog());

        $this->assertLessThanOrEqual($baseline + 5, $searchQueries);
    }

    public function test_site_security_check_komutu_calisir(): void
    {
        $this->artisan('site:security-check')
            ->assertExitCode(0)
            ->expectsOutputToContain('APP_ENV')
            ->expectsOutputToContain('APP_DEBUG')
            ->expectsOutputToContain('Session secure cookie')
            ->expectsOutputToContain('Gizlilik sayfası')
            ->expectsOutputToContain('İletişim rate limit')
            ->expectsOutputToContain('Health bilgi sızdırması');
    }

    public function test_uretimde_adsense_dogrulama_icin_csp_genisletilir(): void
    {
        AdSettings::simulateEnvironment('production');
        AdSettings::setBoolean('adsense_verification_enabled', true);
        AdSettings::setString('adsense_client_id', 'ca-pub-1234567890123456');

        $csp = (string) $this->get(route('home'))->assertOk()->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('pagead2.googlesyndication.com', $csp);
        $this->assertStringContainsString('connect-src', $csp);

        AdSettings::resetSimulation();
    }

    public function test_secure_image_uploader_gecersiz_mime_reddeder(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD eklentisi gerekli.');
        }

        Storage::fake('public');

        $file = UploadedFile::fake()->create('evil.php', 100, 'application/x-php');

        $this->expectException(\RuntimeException::class);

        app(SecureImageUploader::class)->upload($file, 'branding', 256);
    }

    private function createTinyJpeg(): string
    {
        $image = imagecreatetruecolor(4, 4);
        ob_start();
        imagejpeg($image);
        $contents = (string) ob_get_clean();
        imagedestroy($image);

        return $contents;
    }
}
