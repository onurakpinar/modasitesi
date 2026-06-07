<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_istatistikleri_gosterir(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $author = Author::query()->create([
            'name' => 'Yazar',
            'slug' => 'yazar',
            'is_active' => true,
        ]);
        $category = Category::query()->create([
            'name' => 'Test',
            'slug' => 'test',
            'is_active' => true,
        ]);

        Post::query()->create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Yayında Yazı',
            'slug' => 'yayinda-yazi',
            'body' => 'İçerik',
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);

        Post::query()->create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Taslak Yazı',
            'slug' => 'taslak-yazi',
            'body' => 'İçerik',
            'status' => PostStatus::Draft,
        ]);

        ContactMessage::query()->create([
            'name' => 'Ziyaretçi',
            'email' => 'ziyaretci@test.com',
            'subject' => 'Merhaba',
            'message' => 'Test mesajı',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('1', false)
            ->assertSee('Yayında Yazı', false)
            ->assertSee('Taslak Yazı', false);
    }
}
