<?php

namespace App\Support\Seo;

use App\Models\Author;
use App\Models\Category;
use App\Support\PublicContent;
use Illuminate\Support\Facades\Cache;

class SitemapGenerator
{
    public const CACHE_KEY = 'seo.sitemap.xml';

    public const CACHE_TTL = 3600;

    public function get(): string
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->generate());
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function generate(): string
    {
        $urls = [];

        $urls[] = [
            'loc' => SeoSettings::absoluteUrl('/'),
            'lastmod' => now()->toAtomString(),
        ];

        PublicContent::postQuery()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function ($post) use (&$urls) {
                $urls[] = [
                    'loc' => route('posts.show', $post->slug),
                    'lastmod' => $post->updated_at->toAtomString(),
                ];
            });

        Category::query()
            ->active()
            ->withPublishedPosts()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['slug', 'updated_at'])
            ->each(function ($category) use (&$urls) {
                $urls[] = [
                    'loc' => route('categories.show', $category->slug),
                    'lastmod' => $category->updated_at->toAtomString(),
                ];
            });

        Author::query()
            ->active()
            ->whereHas('posts', fn ($query) => $query->publiclyVisible())
            ->orderBy('name')
            ->get(['slug', 'updated_at'])
            ->each(function ($author) use (&$urls) {
                $urls[] = [
                    'loc' => route('authors.show', $author->slug),
                    'lastmod' => $author->updated_at->toAtomString(),
                ];
            });

        PublicContent::publishedStaticPages()
            ->each(function ($page) use (&$urls) {
                $routeName = PublicContent::staticPageRouteName($page->slug);

                if (! $routeName) {
                    return;
                }

                $urls[] = [
                    'loc' => route($routeName),
                    'lastmod' => $page->updated_at->toAtomString(),
                ];
            });

        return view('seo.sitemap', ['urls' => $urls])->render();
    }
}
