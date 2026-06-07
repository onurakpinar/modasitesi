<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\PublicContent;
use App\Support\Seo\SeoBuilder;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $category = Category::query()
            ->active()
            ->withPublishedPosts()
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = PublicContent::postQuery()
            ->with(['author', 'category'])
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(12);

        return view('categories.show', [
            'category' => $category,
            'posts' => $posts,
            'seoMeta' => SeoBuilder::forCategory($category),
        ]);
    }
}
