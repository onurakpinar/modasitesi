<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResponsiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_shell_merkezi_sidebar_state_kullanir(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('x-data="adminShell()"', false)
            ->assertSee('toggleSidebar()', false)
            ->assertSee('closeSidebarOnNavigate()', false)
            ->assertSee('max-w-[85vw]', false);

        $this->actingAs($admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('admin-table-scroll', false);
    }
}
