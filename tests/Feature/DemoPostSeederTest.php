<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Editorial\EditorialBriefCatalog;
use Database\Seeders\DemoPostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoPostSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_post_seeder_30_yayinli_yazi_olusturur(): void
    {
        $this->seed(DemoPostSeeder::class);

        $this->assertSame(30, Post::query()->publiclyVisible()->count());
        $this->assertSame(count(EditorialBriefCatalog::definitions()), Post::query()->count());
    }

    public function test_demo_post_seeder_tekrar_calisinca_yinelenmez(): void
    {
        $this->seed(DemoPostSeeder::class);
        $this->seed(DemoPostSeeder::class);

        $this->assertSame(30, Post::query()->count());
    }

    public function test_demo_yazilarin_kapak_gorseli_vardir(): void
    {
        $this->seed(DemoPostSeeder::class);

        Post::query()->publiclyVisible()->each(function (Post $post): void {
            $this->assertNotNull($post->cover_image);
            $this->assertNotNull($post->cover_image_fallback);
            $this->assertGreaterThanOrEqual(900, str_word_count(strip_tags($post->body)));
        });
    }
}
