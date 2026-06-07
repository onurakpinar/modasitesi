<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\Editorial\EditorialBriefCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportEditorialArticlesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_secenegi_taslak_yazilari_yayinlar(): void
    {
        $this->artisan('site:ensure-content', ['--demo' => true, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(0, Post::query()->publiclyVisible()->count());
        $this->assertSame(30, Post::query()->where('status', PostStatus::Draft)->count());

        $this->artisan('content:import-articles', ['--publish' => true])
            ->expectsOutputToContain('Yayınlanan yazı: 30')
            ->assertSuccessful();

        $this->assertSame(30, Post::query()->publiclyVisible()->count());
        $this->assertSame(0, Post::query()->where('status', PostStatus::Draft)->whereIn(
            'title',
            collect(EditorialBriefCatalog::definitions())->pluck('title_suggestion')->all()
        )->count());
    }
}
