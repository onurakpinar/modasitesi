<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_misafir_admin_paneline_erisemez(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/posts')->assertRedirect(route('admin.login'));
    }

    public function test_giris_sayfasi_goruntulenir(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Giriş Yap', false);
    }

    public function test_aktif_kullanici_giris_yapabilir(): void
    {
        $user = User::factory()->superAdmin()->create([
            'email' => 'admin@modapusula.test',
            'password' => 'SecurePass123!',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@modapusula.test',
            'password' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_pasif_kullanici_giris_yapamaz(): void
    {
        User::factory()->inactive()->create([
            'email' => 'pasif@modapusula.test',
            'password' => 'SecurePass123!',
        ]);

        $this->post('/admin/login', [
            'email' => 'pasif@modapusula.test',
            'password' => 'SecurePass123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_cikis_yapilabilir(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}
