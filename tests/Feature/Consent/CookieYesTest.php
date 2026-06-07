<?php

namespace Tests\Feature\Consent;

use App\Support\Ads\AdSettings;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieYesTest extends TestCase
{
    use RefreshDatabase;

    private const SCRIPT_SNIPPET = 'https://cdn-cookieyes.com/client_data/c77dde366681a7dc23011d73e38d1542/script.js';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
        AdSettings::resetSimulation();
    }

    protected function tearDown(): void
    {
        AdSettings::resetSimulation();

        parent::tearDown();
    }

    public function test_public_layout_yalnizca_tek_cookieyes_scripti_render_eder(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertSame(1, substr_count($html, 'id="cookieyes"'));
        $this->assertSame(1, substr_count($html, self::SCRIPT_SNIPPET));
        $this->assertStringContainsString('<!-- Start cookieyes banner -->', $html);
        $this->assertStringContainsString('<!-- End cookieyes banner -->', $html);
        $this->assertStringNotContainsString('cky-settings.js', $html);
        $this->assertStringNotContainsString('ckySettings', $html);
    }

    public function test_cookieyes_scripti_head_basinda_ve_adsense_oncesi_yuklenir(): void
    {
        $html = $this->get(route('home'))->getContent();

        $headPosition = strpos($html, '<head>');
        $cookieYesPosition = strpos($html, 'id="cookieyes"');
        $charsetPosition = strpos($html, 'charset="utf-8"');
        $siteAssetsPosition = strpos($html, 'build/assets/app-') ?: strpos($html, 'rel="stylesheet"');

        $this->assertNotFalse($headPosition);
        $this->assertNotFalse($cookieYesPosition);
        $this->assertNotFalse($charsetPosition);
        $this->assertGreaterThan($headPosition, $cookieYesPosition);
        $this->assertLessThan($charsetPosition, $cookieYesPosition);

        if ($siteAssetsPosition !== false) {
            $this->assertLessThan($siteAssetsPosition, $cookieYesPosition);
        }
    }

    public function test_admin_layout_cookieyes_scripti_icermez(): void
    {
        $admin = \App\Models\User::factory()->superAdmin()->create();

        $html = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertStringNotContainsString('cdn-cookieyes.com', $html);
    }

    public function test_uretimde_reklam_scriptleri_cookieyes_onayina_ertelenir(): void
    {
        AdSettings::simulateEnvironment('production');
        AdSettings::setBoolean('adsense_verification_enabled', true);
        AdSettings::setBoolean('adsense_ads_enabled', true);
        AdSettings::setBoolean('adsense_auto_ads_enabled', true);
        AdSettings::setBoolean('certified_cmp_configured', true);
        AdSettings::setString('adsense_client_id', 'ca-pub-1234567890123456');

        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString('type="text/plain"', $html);
        $this->assertStringContainsString('data-cookieyes="cookieyes-advertisement"', $html);
        $this->assertStringNotContainsString('data-cookieyes="cookieyes-necessary"', $html);
    }

    public function test_uretimde_cookieyes_icin_csp_domainleri_tanimli(): void
    {
        AdSettings::simulateEnvironment('production');

        $csp = (string) $this->get(route('home'))->assertOk()->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://cdn-cookieyes.com', $csp);
        $this->assertStringContainsString('https://directory.cookieyes.com', $csp);
        $this->assertStringContainsString('https://log.cookieyes.com', $csp);
        $this->assertStringNotContainsString('https://*.cookieyes.com', $csp);
        $this->assertSame(1, substr_count($csp, 'connect-src'));
    }

    public function test_cerez_politikasi_sayfasi_erisilebilir(): void
    {
        $this->get(route('pages.cookies'))
            ->assertOk()
            ->assertSee('Çerez', false);
    }
}
