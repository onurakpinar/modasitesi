<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_kullanicilar_sayfasina_erisemez(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_super_admin_kullanicilar_sayfasina_erisebilir(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Kullanıcılar', false);
    }

    public function test_editor_dashboard_erisebilir(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard', false);
    }
}
