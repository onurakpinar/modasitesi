<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Support\PublicContent;
use App\Support\Seo\SeoBuilder;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();

        $publishedCount = PublicContent::postQuery()
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->count();

        $posts = PublicContent::postQuery()
            ->with(['author', 'category'])
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->latest('published_at')
            ->paginate(12);

        return view('tags.show', [
            'tag' => $tag,
            'posts' => $posts,
            'publishedCount' => $publishedCount,
            'seoMeta' => SeoBuilder::forTag($tag, $publishedCount),
        ]);
    }
}
