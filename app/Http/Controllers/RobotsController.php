<?php

namespace App\Http\Controllers;

use App\Support\Seo\SeoSettings;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $sitemap = SeoSettings::absoluteUrl('/sitemap.xml');

        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /arama',
            '',
            'User-agent: Googlebot',
            'Allow: /yazilar',
            'Allow: /yazi/',
            'Allow: /kategori/',
            'Allow: /etiket/',
            'Allow: /yazar/',
            'Disallow: /admin',
            'Disallow: /arama',
            '',
            'Sitemap: '.$sitemap,
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
