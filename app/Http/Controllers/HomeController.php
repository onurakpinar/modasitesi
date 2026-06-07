<?php

namespace App\Http\Controllers;

use App\Support\HomePageCache;
use App\Support\Seo\SeoBuilder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(HomePageCache $homePageCache): View
    {
        return view('home', [
            ...$homePageCache->get(),
            'seoMeta' => SeoBuilder::forHome(),
        ]);
    }
}
