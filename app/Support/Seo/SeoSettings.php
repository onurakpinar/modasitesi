<?php

namespace App\Support\Seo;

use App\Models\SiteSetting;
use App\Support\MediaUrl;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class SeoSettings
{
    public static function siteName(): string
    {
        return self::get('site_name', config('site.name'));
    }

    public static function defaultTitle(): string
    {
        return self::get('default_meta_title') ?: self::siteName();
    }

    public static function defaultDescription(): string
    {
        return self::get('default_meta_description')
            ?: self::get('site_short_description')
            ?: self::get('site_tagline', config('site.tagline'));
    }

    public static function ogImageUrl(): ?string
    {
        $ogImage = self::get('og_image');
        $logo = self::get('site_logo');

        return MediaUrl::public($ogImage) ?: MediaUrl::public($logo);
    }

    public static function publisherLogoUrl(): ?string
    {
        return MediaUrl::public(self::get('site_logo'));
    }

    public static function absoluteUrl(string $path = '/'): string
    {
        return URL::to($path);
    }

    private static function get(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable('site_settings')) {
            return $default;
        }

        $value = SiteSetting::get($key);

        return filled($value) ? $value : $default;
    }
}
