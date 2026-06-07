<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panelinde_turkce_karakterler_korunur(): void
    {
        $this->seed(CategorySeeder::class);
        $admin = User::factory()->superAdmin()->create();

        $categories = $this->actingAs($admin)
            ->get(route('admin.categories.index'));

        $categories->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('Kadın Modası', false)
            ->assertSee('Sürdürülebilir Moda', false);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSee('İletişim Mesajları', false)
            ->assertSee('Yönetim', false);
    }

    public function test_mobil_sidebar_bilesenleri_mevcut(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('sidebarOpen', false);
        $response->assertSee('Menüyü aç', false);
        $response->assertSee('-translate-x-full', false);
        $response->assertSee('lg:translate-x-0', false);
        $response->assertSee('translate-x-0', false);
    }
}
