<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteAssetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withVite();
        $this->seed(PageSeeder::class);
    }

    public function test_ana_sayfa_vite_ve_font_varliklarini_yukler(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/build/assets/app-', false);
        $response->assertSee('rel="stylesheet"', false);
        $response->assertSee('Playfair Display', false);
        $response->assertSee('@font-face', false);
    }

    public function test_hata_sayfasi_stil_varliklarini_yukler(): void
    {
        $response = $this->get('/olmayan-sayfa-test');

        $response->assertNotFound();
        $response->assertSee('/build/assets/app-', false);
        $response->assertSee('Playfair Display', false);
    }
}
