<?php

namespace Tests\Feature\Deployment;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthIsolationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'unreachable',
            'database.connections.unreachable' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 33079,
                'database' => 'none',
                'username' => 'none',
                'password' => 'none',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
                'options' => [
                    \PDO::ATTR_TIMEOUT => 1,
                ],
            ],
        ]);

        DB::purge('unreachable');
    }

    protected function tearDown(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('unreachable');
        DB::purge('sqlite');

        parent::tearDown();
    }

    public function test_health_db_yokken_http_503_doner_ve_sizdirma_yapmaz(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJsonPath('checks.database', 'unavailable');

        $content = strtolower((string) $response->getContent());
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('stack', $content);
        $this->assertStringNotContainsString('none', $content);
    }

    public function test_up_db_yokken_200_doner(): void
    {
        $this->get('/up')->assertOk();
    }
}
