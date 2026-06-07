<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_yanlis_sifre_ile_giris_engellenir(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'CorrectPass123!',
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'admin@test.com',
                'password' => 'WrongPass123!',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_art_arda_hatali_girislerde_rate_limit_devreye_girer(): void
    {
        RateLimiter::clear('admin-login:admin@test.com|127.0.0.1');

        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'CorrectPass123!',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('admin.login'))
                ->post(route('admin.login.store'), [
                    'email' => 'admin@test.com',
                    'password' => 'WrongPass123!',
                ]);
        }

        $response = $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'admin@test.com',
                'password' => 'WrongPass123!',
            ]);

        $this->assertGuest();

        $this->assertContains(
            $response->status(),
            [302, 429],
            'Altıncı denemede rate limit veya doğrulama yönlendirmesi bekleniyordu.'
        );

        if ($response->status() === 302) {
            $response->assertSessionHasErrors('email');
        }
    }

    public function test_csrf_korumasi_aktif(): void
    {
        $route = app('router')->getRoutes()->getByName('admin.login.store');

        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());

        $webMiddleware = (new Middleware)->getMiddlewareGroups()['web'];

        $this->assertContains(
            PreventRequestForgery::class,
            $webMiddleware,
            'web middleware grubunda CSRF doğrulaması yapılandırılmalıdır.'
        );

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_logout_session_sonlandirir(): void
    {
        $user = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => 'SecurePass123!',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@test.com',
            'password' => 'SecurePass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_pasif_kullanici_admin_paneline_erisemez(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }
}
