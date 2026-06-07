<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SiteEnsureContentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_bos_veritabaninda_otomatik_demo_yayinlamaz(): void
    {
        $this->artisan('site:ensure-content', ['--force' => true])
            ->expectsOutputToContain('Yayınlı yazı yok')
            ->assertSuccessful();

        $this->assertSame(0, Post::query()->publiclyVisible()->count());
    }

    public function test_demo_secenegi_taslak_yukler(): void
    {
        $this->artisan('site:ensure-content', ['--demo' => true, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(0, Post::query()->publiclyVisible()->count());
        $this->assertSame(30, Post::query()->where('status', PostStatus::Draft)->count());
    }

    public function test_icerik_varken_tekrar_demo_seed_etmez(): void
    {
        Artisan::call('site:ensure-content', ['--demo' => true, '--force' => true]);

        Post::query()->first()->update([
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);

        $this->artisan('site:ensure-content', ['--force' => true])
            ->expectsOutputToContain('Yayınlı yazılar mevcut')
            ->assertSuccessful();

        $this->assertSame(30, Post::query()->count());
    }
}
