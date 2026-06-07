<?php

namespace App\Http\Controllers;

use App\Support\PublicContent;
use App\Support\Seo\SeoSettings;
use Illuminate\Http\Response;

class RssController extends Controller
{
    public function index(): Response
    {
        $posts = PublicContent::postQuery()
            ->with(['author', 'category'])
            ->whereNotNull('originality_confirmed_at')
            ->latest('published_at')
            ->limit(20)
            ->get();

        $content = view('seo.rss', [
            'posts' => $posts,
            'siteName' => SeoSettings::siteName(),
            'siteUrl' => SeoSettings::absoluteUrl('/'),
            'feedUrl' => route('rss'),
            'description' => SeoSettings::defaultDescription(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
