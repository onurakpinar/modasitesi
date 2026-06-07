<?php

namespace Tests\Feature\Deployment;

use App\Enums\PostStatus;
use App\Http\Controllers\HealthController;
use App\Models\Post;
use App\Support\Ads\AdSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_veritabani_saglikliyken_200_doner(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'checks' => ['database' => 'ok'],
                'app' => config('site.name'),
            ])
            ->assertJsonStructure(['timestamp'])
            ->assertJsonMissing(['environment', 'password', 'host', 'token', 'secret']);
    }

    public function test_health_endpoint_veritabani_yokken_kontrollu_503_doner(): void
    {
        DB::shouldReceive('connection')
            ->andThrow(new \RuntimeException('secret-host-connection-refused'));

        $response = app(HealthController::class)();

        $this->assertSame(503, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertSame('degraded', $payload['status']);
        $this->assertSame('unavailable', $payload['checks']['database']);
        $this->assertArrayNotHasKey('environment', $payload);

        $encoded = json_encode($payload);
        $this->assertNotFalse($encoded);
        $this->assertStringNotContainsString('secret-host', $encoded);
    }

    public function test_health_route_web_middleware_grubunda_degildir(): void
    {
        $route = Route::getRoutes()->getByName('health');
        $this->assertNotNull($route);

        $middleware = $route->gatherMiddleware();

        $this->assertNotContains('web', $middleware);
        $this->assertNotContains(\Illuminate\Session\Middleware\StartSession::class, $middleware);
    }

    public function test_dockerignore_env_dosyasini_haric_tutar(): void
    {
        $content = file_get_contents(base_path('.dockerignore'));
        $this->assertNotFalse($content);
        $this->assertStringContainsString('.env', $content);
        $this->assertStringContainsString('.env.*', $content);
    }

    public function test_dockerfile_env_kopyalamaz(): void
    {
        $dockerfile = file_get_contents(base_path('Dockerfile'));
        $this->assertNotFalse($dockerfile);
        $this->assertStringContainsString('test ! -f .env', $dockerfile);
        $this->assertStringNotContainsString('COPY .env', $dockerfile);
    }

    public function test_start_script_optimize_kullanir_ve_migration_varsayilan_kapali(): void
    {
        $script = file_get_contents(base_path('docker/start.sh'));
        $this->assertNotFalse($script);
        $this->assertStringContainsString('php artisan optimize', $script);
        $this->assertStringContainsString('storage:link', $script);
        $this->assertStringContainsString('chown -R www-data:www-data', $script);
        $this->assertStringContainsString('RUN_MIGRATIONS:-false', $script);
        $this->assertStringContainsString('APP_KEY', $script);
    }

    public function test_php_artisan_optimize_calisir(): void
    {
        $this->artisan('optimize:clear')->assertExitCode(0);
        $this->artisan('optimize')->assertExitCode(0);
        $this->assertFileExists(base_path('bootstrap/cache/config.php'));

        $this->artisan('optimize:clear')->assertExitCode(0);
    }

    public function test_scheduler_europe_istanbul_saat_dilimini_kullanir(): void
    {
        $this->assertSame('Europe/Istanbul', config('app.timezone'));

        $this->artisan('schedule:list')
            ->assertExitCode(0)
            ->expectsOutputToContain('posts:publish-scheduled');

        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));
        $this->assertNotFalse($bootstrap);
        $this->assertStringContainsString("->timezone(config('app.timezone'))", $bootstrap);
    }

    public function test_zamanlanmis_yazilar_komutla_yayinlanir(): void
    {
        $post = Post::factory()->create([
            'title' => 'Zamanlanmış Deploy Test Yazısı Yeterince Uzun',
            'status' => PostStatus::Scheduled,
            'published_at' => now()->subMinute(),
        ]);

        $this->artisan('posts:publish-scheduled')
            ->assertExitCode(0)
            ->expectsOutputToContain('Zamanlanmış Deploy Test Yazısı');

        $post->refresh();
        $this->assertSame(PostStatus::Published, $post->status);
    }

    public function test_site_readiness_komutu_dogru_rapor_verir(): void
    {
        $this->artisan('site:readiness')
            ->assertExitCode(1)
            ->expectsOutputToContain('PASS')
            ->expectsOutputToContain('WARNING')
            ->expectsOutputToContain('FAIL')
            ->expectsOutputToContain('Google');
    }

    public function test_production_csp_adsense_dogrulama_scriptine_izin_verir(): void
    {
        AdSettings::simulateEnvironment('production');
        AdSettings::setBoolean('adsense_verification_enabled', true);
        AdSettings::setString('adsense_client_id', 'ca-pub-1234567890123456');

        $csp = (string) $this->get(route('home'))->assertOk()->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('pagead2.googlesyndication.com', $csp);

        AdSettings::resetSimulation();
    }

    public function test_deployment_dosyalari_mevcut(): void
    {
        $this->assertFileExists(base_path('Dockerfile'));
        $this->assertFileExists(base_path('docker/start.sh'));
        $this->assertFileExists(base_path('docker/nginx/default.conf'));
        $this->assertFileExists(base_path('docs/deployment.md'));
        $this->assertFileExists(base_path('scripts/deploy-build-test.sh'));
    }

    public function test_deployment_md_zorunlu_bolumleri_icerir(): void
    {
        $content = file_get_contents(base_path('docs/deployment.md'));
        $this->assertNotFalse($content);

        foreach ([
            'Coolify',
            'SQLite',
            'APP_KEY',
            'migrate --force',
            'admin:create',
            'storage:link',
            'schedule:run',
            'yedek',
            'Rollback',
            'AdSense',
        ] as $section) {
            $this->assertStringContainsString($section, $content, "Eksik: {$section}");
        }
    }

    public function test_env_example_uretim_degerlerini_icerir(): void
    {
        $content = file_get_contents(base_path('.env.example'));
        $this->assertNotFalse($content);
        $this->assertStringContainsString('APP_ENV=production', $content);
        $this->assertStringContainsString('APP_DEBUG=false', $content);
        $this->assertStringContainsString('APP_TIMEZONE=Europe/Istanbul', $content);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $content);
        $this->assertStringContainsString('RUN_MIGRATIONS', $content);
    }

    public function test_migrate_force_veri_silmez(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Kalıcı Yayın Başlığı Yeterince Uzun']);

        Artisan::call('migrate', ['--force' => true]);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Kalıcı Yayın Başlığı Yeterince Uzun']);
    }

}
