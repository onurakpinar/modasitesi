<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\Ads\PostBodyAdSplitter;
use App\Support\PublicContent;
use App\Support\Seo\PostSlugRedirector;
use App\Support\Seo\SeoBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('kategori')->toString();

        $query = PublicContent::postQuery()
            ->with(['author', 'category'])
            ->latest('published_at');

        $activeCategory = null;

        if ($categorySlug !== '') {
            $activeCategory = Category::query()
                ->active()
                ->where('slug', $categorySlug)
                ->firstOrFail();

            $query->where('category_id', $activeCategory->id);
        }

        $posts = $query->paginate(12)->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'activeCategory' => $activeCategory,
            'seoMeta' => SeoBuilder::forPostsIndex($activeCategory),
        ]);
    }

    public function show(string $slug, PostSlugRedirector $redirector): View|RedirectResponse
    {
        $post = PublicContent::postQuery()
            ->with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->first();

        if (! $post) {
            $postId = $redirector->resolvePostId($slug);

            if ($postId) {
                $target = PublicContent::postQuery()->find($postId);

                if ($target && $target->slug !== $slug) {
                    return redirect()->route('posts.show', $target->slug, 301);
                }
            }

            abort(404);
        }

        $relatedPosts = PublicContent::postQuery()
            ->with(['author', 'category'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        $bodyParts = PostBodyAdSplitter::split($post->body ?? '');

        return view('posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'seoMeta' => SeoBuilder::forPost($post),
            'postBodyBeforeAd' => $bodyParts['before'],
            'postBodyAfterAd' => $bodyParts['after'],
        ]);
    }
}
