<?php

namespace App\Http\Controllers;

use App\Support\Seo\SitemapGenerator;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(SitemapGenerator $generator): Response
    {
        return response($generator->get(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
