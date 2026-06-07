<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomePageCache
{
    public const CACHE_KEY = 'home.page.content';

    public const CACHE_TTL = 900;

    /**
     * @return array{featuredPosts: Collection<int, Post>, latestPosts: Collection<int, Post>, editorPicks: Collection<int, Post>, categories: Collection<int, Category>}
     */
    public function get(): array
    {
        $cached = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->serializeForCache($this->build()));

        return $this->resolve($cached);
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array{featuredPostIds: array<int, int>, latestPostIds: array<int, int>, editorPickIds: array<int, int>, categoryIds: array<int, int>}  $cached
     * @return array{featuredPosts: Collection<int, Post>, latestPosts: Collection<int, Post>, editorPicks: Collection<int, Post>, categories: Collection<int, Category>}
     */
    private function resolve(array $cached): array
    {
        $postIds = array_values(array_unique(array_merge(
            $cached['featuredPostIds'],
            $cached['latestPostIds'],
            $cached['editorPickIds'],
        )));

        $posts = $postIds === []
            ? collect()
            : PublicContent::postQuery()
                ->with(['author', 'category'])
                ->whereIn('id', $postIds)
                ->get()
                ->keyBy('id');

        $categories = $cached['categoryIds'] === []
            ? collect()
            : PublicContent::categoryNavQuery()
                ->withCount(['posts' => fn ($query) => $query->publiclyVisible()])
                ->whereIn('id', $cached['categoryIds'])
                ->get()
                ->keyBy('id');

        return [
            'featuredPosts' => $this->orderPosts($posts, $cached['featuredPostIds']),
            'latestPosts' => $this->orderPosts($posts, $cached['latestPostIds']),
            'editorPicks' => $this->orderPosts($posts, $cached['editorPickIds']),
            'categories' => $this->orderCategories($categories, $cached['categoryIds']),
        ];
    }

    /**
     * @param  Collection<int|string, Post>  $posts
     * @param  array<int, int>  $ids
     * @return Collection<int, Post>
     */
    private function orderPosts(Collection $posts, array $ids): Collection
    {
        return collect($ids)
            ->map(fn (int $id) => $posts->get($id))
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int|string, Category>  $categories
     * @param  array<int, int>  $ids
     * @return Collection<int, Category>
     */
    private function orderCategories(Collection $categories, array $ids): Collection
    {
        return collect($ids)
            ->map(fn (int $id) => $categories->get($id))
            ->filter()
            ->values();
    }

    /**
     * @param  array{featuredPosts: Collection<int, Post>, latestPosts: Collection<int, Post>, editorPicks: Collection<int, Post>, categories: Collection<int, Category>}  $data
     * @return array{featuredPostIds: array<int, int>, latestPostIds: array<int, int>, editorPickIds: array<int, int>, categoryIds: array<int, int>}
     */
    private function serializeForCache(array $data): array
    {
        return [
            'featuredPostIds' => $data['featuredPosts']->pluck('id')->all(),
            'latestPostIds' => $data['latestPosts']->pluck('id')->all(),
            'editorPickIds' => $data['editorPicks']->pluck('id')->all(),
            'categoryIds' => $data['categories']->pluck('id')->all(),
        ];
    }

    /**
     * @return array{featuredPosts: Collection<int, Post>, latestPosts: Collection<int, Post>, editorPicks: Collection<int, Post>, categories: Collection<int, Category>}
     */
    private function build(): array
    {
        $recentPosts = PublicContent::postQuery()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(24)
            ->get();

        $featuredPosts = $recentPosts->where('is_featured', true)->take(3)->values();

        $latestPosts = $recentPosts->take(6)->values();

        $editorPicks = $recentPosts
            ->where('is_featured', true)
            ->slice($featuredPosts->count())
            ->take(4)
            ->values();

        if ($editorPicks->isEmpty()) {
            $editorPicks = $recentPosts
                ->whereNotIn('id', $featuredPosts->pluck('id'))
                ->take(4)
                ->values();
        }

        $categories = PublicContent::categoryNavQuery()
            ->withCount(['posts' => fn ($query) => $query->publiclyVisible()])
            ->get();

        return compact('featuredPosts', 'latestPosts', 'editorPicks', 'categories');
    }
}
