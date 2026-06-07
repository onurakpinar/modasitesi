<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\Ads\PageTemplates;
use App\Support\Legal\LegalPlaceholders;
use App\Support\PublicContent;
use App\Support\Seo\SeoBuilder;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $allowedSlugs = array_values(PublicContent::staticPageRoutes());

        if (! in_array($slug, $allowedSlugs, true)) {
            throw new NotFoundHttpException;
        }

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        if (! PageTemplates::isPublicReady($page->body ?? '')) {
            throw new NotFoundHttpException;
        }

        $labels = PublicContent::staticPageLabels();

        $siteContactEmail = Schema::hasTable('site_settings')
            ? SiteSetting::query()->where('key', 'contact_email')->value('value')
            : null;

        return view('pages.show', [
            'page' => $page,
            'pageLabel' => $labels[$slug] ?? $page->title,
            'isContactPage' => $slug === 'iletisim',
            'pageBody' => PageTemplates::renderBody($page->body ?? ''),
            'contactEmail' => LegalPlaceholders::effectiveContactEmail($siteContactEmail),
            'seoMeta' => SeoBuilder::forPage($page),
        ]);
    }
}
