<?php

namespace Tests\Feature\Ads;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Ads\AdEligibility;
use App\Support\Ads\AdSettings;
use App\Support\Ads\AdSenseValidator;
use App\Support\Ads\PostBodyAdSplitter;
use App\Support\PostQualityChecker;
use App\Support\PublicContent;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishablePostPayload;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class AdSenseModuleTest extends TestCase
{
    use PublishablePostPayload;
    use PublishesStaticPages;
    use RefreshDatabase;

    private const CLIENT_ID = 'ca-pub-1234567890123456';

    private const PUBLISHER_ID = 'pub-1234567890123456';

    private const MIDDLE_SLOT = '1234567890';

    private const BOTTOM_SLOT = '0987654321';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
        URL::forceRootUrl('https://moda.test');
        URL::forceScheme('https');
        AdSettings::resetSimulation();
    }

    public function test_dogrulama_scripti_test_ortaminda_yuklenmez(): void
    {
        AdSettings::setBoolean('adsense_verification_enabled', true);
        AdSettings::setString('adsense_client_id', self::CLIENT_ID);

        $html = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('googlesyndication.com', $html);
        $this->assertStringContainsString('AdSense doğrulama scripti yerel/test ortamında yüklenmez', $html);
    }

    public function test_gecersiz_client_id_reddedilir(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->put(route('admin.adsense.update'), [
                'adsense_client_id' => 'evil-script',
            ])
            ->assertSessionHasErrors('adsense_client_id');
    }

    public function test_reklamlar_cmp_olmadan_acilamaz(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->put(route('admin.adsense.update'), [
                'adsense_ads_enabled' => '1',
                'certified_cmp_configured' => '0',
            ])
            ->assertSessionHasErrors('adsense_ads_enabled');
    }

    public function test_iletisim_bilgisi_eposta_olmadan_tamamlanamaz(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->publishStaticPagesForTests();
        config(['legal.contact_email' => '']);
        SiteSetting::set('contact_email', '');

        $this->actingAs($admin)
            ->put(route('admin.adsense.update'), [
                'contact_information_completed' => '1',
            ])
            ->assertSessionHasErrors('contact_information_completed');
    }

    public function test_reklam_kutulari_varsayilan_olarak_render_edilmez(): void
    {
        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(700),
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();

        $this->assertStringNotContainsString('adsbygoogle', $html);
        $this->assertStringNotContainsString('data-ad-slot', $html);
    }

    public function test_reklam_kutulari_yalnizca_tum_kosullar_saglandiginda_gosterilir(): void
    {
        $this->enableProductionAds();

        $post = Post::factory()->published()->create([
            'slug' => 'reklamli-yazi',
            'body' => $this->bodyWithWordCount(950),
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();

        $this->assertStringContainsString('Advertisement', $html);
        $this->assertStringContainsString('data-ad-slot="'.self::MIDDLE_SLOT.'"', $html);
        $this->assertStringContainsString('data-ad-slot="'.self::BOTTOM_SLOT.'"', $html);
        $this->assertStringNotContainsString('Buraya tıklayın', $html);
        $this->assertStringNotContainsString('Destek olun', $html);
    }

    public function test_kisa_yazida_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();

        $post = Post::factory()->published()->create([
            'body' => '<p>'.implode(' ', array_fill(0, 100, 'kisa')).'</p>',
        ]);

        $this->assertFalse(AdEligibility::canShowArticleAds($post));

        $html = $this->get(route('posts.show', $post->slug))->getContent();
        $this->assertStringNotContainsString('data-ad-slot', $html);
    }

    public function test_ana_sayfa_ve_arama_sayfasinda_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();

        $this->get(route('home'))->assertDontSee('data-ad-slot', false);
        $this->get(route('search'))->assertDontSee('data-ad-slot', false);
        $this->get(route('posts.index'))->assertDontSee('data-ad-slot', false);
    }

    public function test_placeholder_iceren_sabit_sayfa_publicte_404_doner(): void
    {
        Page::query()->where('slug', 'kullanim-kosullari')->update([
            'status' => PageStatus::Published,
            'body' => '<p>İçerik [BILINMEYEN_YER_TUTUCU] ile tamamlanmamış.</p>',
        ]);

        $this->get(route('pages.terms'))->assertNotFound();
    }

    public function test_doldurulmus_sabit_sayfa_yayinlanabilir(): void
    {
        $this->publishStaticPagesForTests();

        $this->get(route('pages.about'))
            ->assertOk()
            ->assertDontSee('[ŞİRKET_ADI]', false);
    }

    public function test_ads_txt_kok_dizinde_mevcut(): void
    {
        $this->assertFileExists(public_path('ads.txt'));

        $content = $this->get(route('ads.txt'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->getContent();

        $this->assertStringContainsString(
            'google.com, pub-4108324995084946, DIRECT, f08c47fec0942fa0',
            $content
        );
    }

    public function test_ads_txt_gecerli_publisher_id_ile_uretilir(): void
    {
        $this->get(route('ads.txt'))
            ->assertOk()
            ->assertSee('google.com, pub-4108324995084946, DIRECT, f08c47fec0942fa0', false);
    }

    public function test_admin_adsense_paneli_erisilebilir(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.adsense.edit'))
            ->assertOk()
            ->assertSee('AdSense Başvuru Hazırlık Kontrolü', false)
            ->assertSee('garanti edilmez', false);
    }

    public function test_validator_formatlari(): void
    {
        $this->assertTrue(AdSenseValidator::isValidClientId(self::CLIENT_ID));
        $this->assertFalse(AdSenseValidator::isValidClientId('<script>'));
        $this->assertTrue(AdSenseValidator::isValidPublisherId(self::PUBLISHER_ID));
        $this->assertTrue(AdSenseValidator::isValidSlotId(self::MIDDLE_SLOT));
        $this->assertFalse(AdSenseValidator::isValidSlotId('abc'));
    }

    public function test_icerik_dort_paragraftan_sonra_bolunur(): void
    {
        $html = '<p>1</p><p>2</p><p>3</p><p>4</p><p>5</p>';
        $parts = PostBodyAdSplitter::split($html);

        $this->assertSame('<p>1</p><p>2</p><p>3</p><p>4</p>', $parts['before']);
        $this->assertSame('<p>5</p>', $parts['after']);
    }

    public function test_preview_sayfasinda_reklam_gosterilmez(): void
    {
        $this->enableProductionAds();

        $admin = User::factory()->superAdmin()->create();
        $post = Post::factory()->published()->create([
            'body' => $this->bodyWithWordCount(700),
        ]);

        $previewUrl = URL::temporarySignedRoute('admin.posts.preview', now()->addHour(), ['post' => $post->id]);

        $this->actingAs($admin)
            ->get($previewUrl)
            ->assertDontSee('data-ad-slot', false);
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
