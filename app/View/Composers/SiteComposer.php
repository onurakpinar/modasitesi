<?php

namespace App\View\Composers;

use App\Models\SiteSetting;
use App\Support\PublicContent;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SiteComposer
{
    public function compose(View $view): void
    {
        $view->with($this->sharedData());
    }

    /**
     * @return array<string, mixed>
     */
    private function sharedData(): array
    {
        return once(function () {
            $settings = $this->settings();

            return [
                'siteName' => $settings['site_name'] ?? config('site.name'),
                'siteTagline' => $settings['site_tagline'] ?? config('site.tagline'),
                'siteShortDescription' => $settings['site_short_description'] ?? '',
                'footerDescription' => $settings['footer_description'] ?? '',
                'contactEmail' => filled($settings['contact_email'] ?? null) ? $settings['contact_email'] : null,
                'siteLogo' => $settings['site_logo'] ?? null,
                'siteFavicon' => $settings['site_favicon'] ?? null,
                'socialLinks' => [
                    'instagram' => $settings['social_instagram'] ?? null,
                    'facebook' => $settings['social_facebook'] ?? null,
                    'pinterest' => $settings['social_pinterest'] ?? null,
                    'twitter' => $settings['social_twitter'] ?? null,
                ],
                'navCategories' => $this->navCategories(),
                'footerCategories' => $this->navCategories(),
                'staticPages' => $this->staticPages(),
            ];
        });
    }

    /**
     * @return array<string, string>
     */
    private function settings(): array
    {
        return once(function () {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return SiteSetting::query()->pluck('value', 'key')->all();
        });
    }

    private function navCategories()
    {
        return once(function () {
            if (! Schema::hasTable('categories')) {
                return collect();
            }

            return PublicContent::categoryNavQuery()->get(['id', 'name', 'slug']);
        });
    }

    private function staticPages()
    {
        return once(function () {
            if (! Schema::hasTable('pages')) {
                return collect();
            }

            return PublicContent::publishedStaticPages();
        });
    }
}
