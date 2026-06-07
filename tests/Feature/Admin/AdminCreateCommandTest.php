<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_ilk_kullanici_super_admin_olur(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Test Admin',
            '--email' => 'admin@test.com',
            '--password' => 'SecurePass1234',
        ])->assertSuccessful();

        $user = User::query()->where('email', 'admin@test.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::SuperAdmin, $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_sonraki_kullanici_editor_olur(): void
    {
        User::factory()->superAdmin()->create();

        $this->artisan('admin:create', [
            '--name' => 'Editör',
            '--email' => 'editor@test.com',
            '--password' => 'SecurePass1234',
        ])->assertSuccessful();

        $this->assertSame(
            UserRole::Editor,
            User::query()->where('email', 'editor@test.com')->value('role')
        );
    }
}
