<?php

namespace App\Http\Controllers;

use App\Support\PublicContent;
use App\Support\Seo\SeoBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = strip_tags($request->string('q')->trim()->toString());
        $query = mb_substr($query, 0, 100);

        $posts = null;

        if ($query !== '') {
            $posts = PublicContent::postQuery()
                ->with(['author', 'category'])
                ->where(function ($builder) use ($query) {
                    $builder->where('title', 'like', '%'.$query.'%')
                        ->orWhere('excerpt', 'like', '%'.$query.'%')
                        ->orWhere('body', 'like', '%'.$query.'%');
                })
                ->latest('published_at')
                ->paginate(12)
                ->withQueryString();
        }

        return view('search.index', [
            'query' => $query,
            'posts' => $posts,
            'seoMeta' => SeoBuilder::forSearch($query !== '' ? $query : null),
        ]);
    }
}
