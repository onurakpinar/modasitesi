<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Support\PublicContent;
use App\Support\Seo\SeoBuilder;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(string $slug): View
    {
        $author = Author::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $publishedCount = PublicContent::postQuery()
            ->where('author_id', $author->id)
            ->count();

        $posts = PublicContent::postQuery()
            ->with(['category'])
            ->where('author_id', $author->id)
            ->latest('published_at')
            ->paginate(12);

        return view('authors.show', [
            'author' => $author,
            'posts' => $posts,
            'publishedCount' => $publishedCount,
            'seoMeta' => SeoBuilder::forAuthor($author, $publishedCount),
        ]);
    }
}
