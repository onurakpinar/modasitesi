<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_migration_ve_seeder_calisir(): void
    {
        $this->seed(CategorySeeder::class);

        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('posts'));
        $this->assertTrue(Schema::hasTable('post_tag'));
        $this->assertTrue(Schema::hasTable('post_revisions'));
        $this->assertTrue(Schema::hasTable('site_settings'));
        $this->assertTrue(Schema::hasTable('content_briefs'));
        $this->assertTrue(Schema::hasColumns('users', ['role', 'is_active', 'last_login_at']));
        $this->assertSame(6, Category::query()->count());
    }

    public function test_rollback_ve_yeniden_migrate_calisir(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 11])->assertSuccessful();
        $this->assertFalse(Schema::hasTable('posts'));
        $this->assertFalse(Schema::hasTable('categories'));

        $this->artisan('migrate')->assertSuccessful();
        $this->assertTrue(Schema::hasTable('posts'));
        $this->assertTrue(Schema::hasTable('categories'));
    }
}
