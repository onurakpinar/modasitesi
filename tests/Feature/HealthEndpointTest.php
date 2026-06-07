<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_json_doner(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'checks' => ['database'],
            'app',
            'timestamp',
        ]);
        $response->assertJson([
            'status' => 'ok',
            'checks' => ['database' => 'ok'],
            'app' => config('site.name'),
        ]);
        $response->assertJsonMissing(['environment' => 'testing']);
    }
}
