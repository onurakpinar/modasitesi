<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Support\Ads\PageTemplates;
use Illuminate\Database\Eloquent\Builder;

class PublicContent
{
    /**
     * @return array<string, string>
     */
    public static function staticPageRoutes(): array
    {
        return [
            'pages.about' => 'hakkimizda',
            'pages.contact' => 'iletisim',
            'pages.privacy' => 'gizlilik-politikasi',
            'pages.cookies' => 'cerez-politikasi',
            'pages.terms' => 'kullanim-kosullari',
            'pages.editorial' => 'yayin-ilkeleri',
            'pages.corrections' => 'duzeltme-politikasi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function staticPageRouteName(string $slug): ?string
    {
        foreach (self::staticPageRoutes() as $routeName => $routeSlug) {
            if ($routeSlug === $slug) {
                return $routeName;
            }
        }

        return null;
    }

    public static function staticPageLabels(): array
    {
        return [
            'hakkimizda' => 'Hakkımızda',
            'iletisim' => 'İletişim',
            'gizlilik-politikasi' => 'Gizlilik Politikası',
            'cerez-politikasi' => 'Çerez Politikası',
            'kullanim-kosullari' => 'Kullanım Koşulları',
            'yayin-ilkeleri' => 'Yayın İlkeleri',
            'duzeltme-politikasi' => 'Düzeltme Politikası',
        ];
    }

    public static function postQuery(): Builder
    {
        return Post::query()->publiclyVisible();
    }

    public static function categoryNavQuery(): Builder
    {
        return Category::query()
            ->active()
            ->withPublishedPosts()
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Page>
     */
    public static function publishedStaticPages()
    {
        $slugs = array_values(self::staticPageRoutes());

        return Page::query()
            ->published()
            ->whereIn('slug', $slugs)
            ->get()
            ->filter(fn (Page $page) => PageTemplates::isPublicReady($page->body ?? ''))
            ->sortBy(fn (Page $page) => array_search($page->slug, $slugs, true))
            ->values();
    }
}
