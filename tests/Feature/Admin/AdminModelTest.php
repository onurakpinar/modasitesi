<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_iliskileri_dogru_calisir(): void
    {
        $author = Author::query()->create([
            'name' => 'Yazar',
            'slug' => 'yazar',
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Kategori',
            'slug' => 'kategori',
            'is_active' => true,
        ]);

        $tag = Tag::query()->create([
            'name' => 'Etiket',
            'slug' => 'etiket',
        ]);

        $post = Post::query()->create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Test Yazı',
            'slug' => 'test-yazi',
            'body' => 'İçerik',
            'status' => PostStatus::Draft,
        ]);

        $post->tags()->attach($tag->id);

        $user = User::factory()->create();

        PostRevision::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
        ]);

        $post->load(['author', 'category', 'tags', 'revisions']);

        $this->assertTrue($post->author->is($author));
        $this->assertTrue($post->category->is($category));
        $this->assertCount(1, $post->tags);
        $this->assertTrue($post->tags->first()->is($tag));
        $this->assertCount(1, $post->revisions);
        $this->assertSame($user->id, $post->revisions->first()->user_id);
    }

    public function test_post_soft_delete_calisir(): void
    {
        $author = Author::query()->create([
            'name' => 'Yazar',
            'slug' => 'yazar',
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Kategori',
            'slug' => 'kategori',
            'is_active' => true,
        ]);

        $post = Post::query()->create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Silinecek',
            'slug' => 'silinecek',
            'body' => 'İçerik',
            'status' => PostStatus::Draft,
        ]);

        $postId = $post->id;
        $post->delete();

        $this->assertSoftDeleted('posts', ['id' => $postId]);
        $this->assertNull(Post::query()->find($postId));
        $this->assertNotNull(Post::withTrashed()->find($postId));
    }
}
