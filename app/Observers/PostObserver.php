<?php

namespace App\Observers;

use App\Models\Post;
use App\Support\HomePageCache;
use App\Support\Seo\SitemapGenerator;

class PostObserver
{
    public function saved(Post $post): void
    {
        $this->forgetCaches();
    }

    public function deleted(Post $post): void
    {
        $this->forgetCaches();
    }

    public function forceDeleted(Post $post): void
    {
        $this->forgetCaches();
    }

    private function forgetCaches(): void
    {
        app(SitemapGenerator::class)->forget();
        app(HomePageCache::class)->forget();
    }
}
