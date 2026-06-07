<?php

namespace Tests\Feature\Consent;

use App\Support\Ads\AdSettings;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieYesTest extends TestCase
{
    use RefreshDatabase;

    private const SITE_ID = 'c77dde366681a7dc23011d73e38d1542';

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

    public function test_yerel_ortamda_cookieyes_yuklenmez(): void
    {
        config([
            'cookieyes.enabled' => true,
            'cookieyes.site_id' => self::SITE_ID,
        ]);

        $html = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('cdn-cookieyes.com', $html);
    }

    public function test_uretimde_cookieyes_banner_yuklenir(): void
    {
        AdSettings::simulateEnvironment('production');

        config([
            'cookieyes.enabled' => true,
            'cookieyes.site_id' => self::SITE_ID,
        ]);

        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString('id="cookieyes"', $html);
        $this->assertStringContainsString(
            'https://cdn-cookieyes.com/client_data/'.self::SITE_ID.'/script.js',
            $html
        );
    }

    public function test_uretimde_cookieyes_icin_csp_genisletilir(): void
    {
        AdSettings::simulateEnvironment('production');

        config([
            'cookieyes.enabled' => true,
            'cookieyes.site_id' => self::SITE_ID,
        ]);

        $csp = (string) $this->get(route('home'))->assertOk()->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://cdn-cookieyes.com', $csp);
        $this->assertStringContainsString('https://directory.cookieyes.com', $csp);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline' https://cdn-cookieyes.com", $csp);
        $this->assertSame(1, substr_count($csp, 'connect-src'));
    }

    public function test_cookieyes_head_icinde_erken_yuklenir(): void
    {
        AdSettings::simulateEnvironment('production');

        config([
            'cookieyes.enabled' => true,
            'cookieyes.site_id' => self::SITE_ID,
        ]);

        $html = $this->get(route('home'))->getContent();
        $viewportPosition = strpos($html, 'name="viewport"');
        $cookieYesPosition = strpos($html, 'id="cookieyes"');
        $titlePosition = strpos($html, '<title>');

        $this->assertNotFalse($viewportPosition);
        $this->assertNotFalse($cookieYesPosition);
        $this->assertNotFalse($titlePosition);
        $this->assertGreaterThan($viewportPosition, $cookieYesPosition);
        $this->assertLessThan($titlePosition, $cookieYesPosition);
    }
}
