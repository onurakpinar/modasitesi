<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_ana_sayfa_basariyla_yuklenir(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(config('site.name'), false);
        $response->assertSee('Moda yayını', false)
            ->assertSee('Yazılar', false);
    }
}
