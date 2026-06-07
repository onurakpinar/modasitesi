<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductionErrorPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);
    }

    public function test_uretim_modunda_404_ozel_sayfa_gosterir(): void
    {
        $response = $this->get('/paket1-test-404');

        $response->assertNotFound();
        $response->assertSee('Sayfa bulunamadı', false);
        $response->assertDontSee('Whoops', false);
    }

    public function test_uretim_modunda_500_ozel_sayfa_gosterir(): void
    {
        Route::get('/paket1-test-500', fn () => abort(500));

        $response = $this->get('/paket1-test-500');

        $response->assertStatus(500);
        $response->assertSee('Bir hata oluştu', false);
        $response->assertDontSee('Whoops', false);
    }
}
