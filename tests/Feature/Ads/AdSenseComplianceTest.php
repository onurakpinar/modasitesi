<?php

namespace Tests\Feature\Ads;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Ads\AdSenseReadinessChecker;
use App\Support\Ads\AdSettings;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishablePostPayload;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class AdSenseComplianceTest extends TestCase
{
    use PublishablePostPayload;
    use PublishesStaticPages;
    use RefreshDatabase;

    private const CLIENT_ID = 'ca-pub-1234567890123456';

    private const PUBLISHER_ID = 'pub-1234567890123456';

    private const MIDDLE_SLOT = '1234567890';

    private const BOTTOM_SLOT = '0987654321';

    /** @var array<int, string> */
    private const FORBIDDEN_PUBLIC_AD_COPY = [
        'Buraya tıklayın',
        'Destek olun',
        'Önerilen bağlantılar',
        'Kaynaklar',
        'tıklayarak destek',
        'reklama tıkla',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
        URL::forceRootUrl('https://moda.test');
        URL::forceScheme('https');
        AdSettings::resetSimulation();
    }

  public function test_01_lokal_ortamda_gercek_adsense_scripti_yuklenmez(): void
    {
        $this->configureVerificationScript(environment: 'local');

        $html = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('pagead2.googlesyndication.com', $html);
        $this->assertStringContainsString('AdSense doğrulama scripti yerel/test ortamında yüklenmez', $html);
    }

    public function test_02_testing_ortaminda_script_yuklenmez(): void
    {
        $this->configureVerificationScript(environment: 'testing');

        $html = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('pagead2.googlesyndication.com', $html);
    }

    public function test_03_productionda_verification_kapali_iken_script_yuklenmez(): void
    {
        AdSettings::simulateEnvironment('production');
        AdSettings::setBoolean('adsense_verification_enabled', false);
        AdSettings::setString('adsense_client_id', self::CLIENT_ID);

        $html = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('pagead2.googlesyndication.com', $html);
    }

    public function test_04_gecersiz_client_id_kaydedilemez(): void
    {
        $admin = User::factory()->superAdmin()->create();
        SiteSetting::set('adsense_client_id', self::CLIENT_ID, 'adsense');

        $this->actingAs($admin)
            ->put(route('admin.adsense.update'), [
                'adsense_client_id' => '<script>alert(1)</script>',
            ])
            ->assertSessionHasErrors('adsense_client_id');

        $this->assertSame(self::CLIENT_ID, SiteSetting::get('adsense_client_id'));
        $this->assertSame(self::CLIENT_ID, AdSettings::clientId());
    }

    public function test_05_script_etiketi_admin_alaninda_calismaz(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $payload = '<script>alert("xss")</script>';

        $this->actingAs($admin)
            ->put(route('admin.adsense.update'), [
                'adsense_client_id' => $payload,
            ])
            ->assertSessionHasErrors('adsense_client_id');

        $this->assertNotSame($payload, SiteSetting::get('adsense_client_id'));

        $this->actingAs($admin)
            ->get(route('admin.adsense.edit'))
            ->assertOk()
            ->assertDontSee($payload, false);
    }

    public function test_06_cmp_configured_false_iken_reklam_kutusu_cikmaz(): void
    {
        AdSettings::simulateEnvironment('production');
        AdSettings::setBoolean('adsense_ads_enabled', true);
        AdSettings::setBoolean('certified_cmp_configured', false);
        AdSettings::setString('adsense_client_id', self::CLIENT_ID);
        AdSettings::setString('adsense_article_middle_slot', self::MIDDLE_SLOT);
        AdSettings::setString('adsense_article_bottom_slot', self::BOTTOM_SLOT);

        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(950),
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();

        $this->assertStringNotContainsString('data-ad-slot', $html);
        $this->assertStringNotContainsString('adsbygoogle', $html);
    }

    public function test_07_699_kelimelik_yazida_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();

        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(699),
        ]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_08_preview_sayfasinda_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();

        $admin = User::factory()->superAdmin()->create();
        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(950),
        ]);

        $previewUrl = URL::temporarySignedRoute('admin.posts.preview', now()->addHour(), ['post' => $post->id]);

        $this->actingAs($admin)
            ->get($previewUrl)
            ->assertOk()
            ->assertDontSee('data-ad-slot', false)
            ->assertDontSee('Advertisement', false);
    }

    public function test_09_iletisim_ve_gizlilik_sayfasinda_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();
        $this->publishStaticPagesForTests();

        foreach ([route('pages.contact'), route('pages.privacy')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('data-ad-slot', $html, "Reklam bulundu: {$url}");
            $this->assertStringNotContainsString('adsbygoogle', $html, "Reklam bulundu: {$url}");
        }
    }

    public function test_10_arama_sonuclarinda_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();

        Post::factory()->published()->create([
            'title' => 'Arama Test Yazısı',
            'body' => $this->bodyWithWordCount(950),
        ]);

        $this->get(route('search', ['q' => 'Arama']))
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_11_404_sayfasinda_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();

        $this->get('/olmayan-sayfa-ads-test')
            ->assertNotFound()
            ->assertDontSee('data-ad-slot', false)
            ->assertDontSee('pagead2.googlesyndication.com', false);
    }

    public function test_12_advertisement_etiketi_dogru_kullanilir(): void
    {
        $this->enableProductionAds();

        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(950),
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();

        $this->assertStringContainsString('aria-label="Advertisement"', $html);
        $this->assertStringContainsString('>Advertisement</p>', $html);
        $this->assertStringNotContainsString('>Reklam</p>', $html);
        $this->assertStringNotContainsString('Sponsorlu', $html);
    }

    public function test_13_adsense_scripti_tek_sefer_yuklenir(): void
    {
        $this->configureVerificationScript(environment: 'production');

        $html = $this->get(route('home'))->getContent();

        $this->assertSame(1, substr_count($html, 'pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'));
    }

    public function test_14_reklam_alani_mobil_tasmayi_onler(): void
    {
        $this->enableProductionAds();

        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(950),
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();

        $this->assertStringContainsString('class="ad-slot my-12 max-w-full overflow-hidden"', $html);
        $this->assertStringContainsString('data-full-width-responsive="true"', $html);
    }

    public function test_15_ads_txt_dogru_content_type_doner(): void
    {
        $this->get(route('ads.txt'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_16_publisher_id_yokken_sahte_id_olusturulmaz(): void
    {
        $content = $this->get(route('ads.txt'))->getContent();

        $this->assertStringNotContainsString('google.com, pub-', $content);
    }

    public function test_17_gercek_publisher_id_ile_ads_txt_satiri_dogru(): void
    {
        AdSettings::setString('adsense_publisher_id', self::PUBLISHER_ID);

        $this->get(route('ads.txt'))
            ->assertOk()
            ->assertSee('google.com, '.self::PUBLISHER_ID.', DIRECT, f08c47fec0942fa0', false);
    }

    public function test_18_privacy_sayfasi_eksikken_readiness_hata_gosterir(): void
    {
        AdSettings::setBoolean('privacy_policy_completed', false);

        $checks = collect(app(AdSenseReadinessChecker::class)->checks());
        $privacy = $checks->firstWhere('key', 'privacy_policy');

        $this->assertNotNull($privacy);
        $this->assertFalse($privacy['passed']);
    }

    public function test_19_lorem_ipsum_icerik_readiness_den_gecemez(): void
    {
        Page::query()->where('slug', 'hakkimizda')->update([
            'body' => '<p>Lorem ipsum dolor sit amet.</p>',
            'status' => PageStatus::Published,
        ]);

        $checks = collect(app(AdSenseReadinessChecker::class)->checks());
        $emptyPages = $checks->firstWhere('key', 'empty_pages');

        $this->assertNotNull($emptyPages);
        $this->assertFalse($emptyPages['passed']);
    }

    public function test_20_tiklama_tesviki_veya_yaniltici_reklam_metni_yok(): void
    {
        $this->enableProductionAds();

        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(950),
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();

        foreach (self::FORBIDDEN_PUBLIC_AD_COPY as $phrase) {
            $this->assertStringNotContainsString($phrase, $html);
        }
    }

    private function configureVerificationScript(string $environment): void
    {
        AdSettings::simulateEnvironment($environment);
        AdSettings::setBoolean('adsense_verification_enabled', true);
        AdSettings::setString('adsense_client_id', self::CLIENT_ID);
    }

    private function enableProductionAds(): void
    {
        AdSettings::simulateEnvironment('production');

        AdSettings::setBoolean('adsense_ads_enabled', true);
        AdSettings::setBoolean('certified_cmp_configured', true);
        AdSettings::setString('adsense_client_id', self::CLIENT_ID);
        AdSettings::setString('adsense_article_middle_slot', self::MIDDLE_SLOT);
        AdSettings::setString('adsense_article_bottom_slot', self::BOTTOM_SLOT);
    }
}
