<?php

namespace Tests\Feature\Content;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\Editorial\EditorialBriefCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnpublishFlaggedPostsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_baslikli_yayinli_yazilari_taslag_ceker(): void
    {
        $title = EditorialBriefCatalog::definitions()[0]['title_suggestion'];

        Post::factory()->published()->create([
            'title' => $title,
            'slug' => 'demo-slug-test',
        ]);

        $this->artisan('content:unpublish-flagged')
            ->assertSuccessful();

        $this->assertSame(PostStatus::Draft, Post::query()->first()->status);
        $this->assertSame(0, Post::query()->publiclyVisible()->count());
    }
}
