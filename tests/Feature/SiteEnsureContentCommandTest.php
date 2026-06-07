<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SiteEnsureContentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_bos_veritabaninda_demo_icerik_yukler(): void
    {
        $this->artisan('site:ensure-content', ['--force' => true])
            ->assertSuccessful();

        $this->assertSame(30, Post::query()->publiclyVisible()->count());
    }

    public function test_icerik_varken_tekrar_seed_etmez(): void
    {
        Artisan::call('site:ensure-content', ['--force' => true]);

        $this->artisan('site:ensure-content', ['--force' => true])
            ->expectsOutputToContain('Yayınlı yazılar mevcut')
            ->assertSuccessful();

        $this->assertSame(30, Post::query()->count());
    }
}
