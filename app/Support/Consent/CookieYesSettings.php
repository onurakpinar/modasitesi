<?php

namespace App\Support\Consent;

use App\Support\Ads\AdSettings;

class CookieYesSettings
{
    public static function enabled(): bool
    {
        return (bool) config('cookieyes.enabled', false);
    }

    public static function siteId(): ?string
    {
        $siteId = config('cookieyes.site_id');

        if (! is_string($siteId) || ! preg_match('/^[a-f0-9]{32}$/', $siteId)) {
            return null;
        }

        return $siteId;
    }

    public static function scriptUrl(): ?string
    {
        $siteId = self::siteId();

        if ($siteId === null) {
            return null;
        }

        return "https://cdn-cookieyes.com/client_data/{$siteId}/script.js";
    }

    public static function shouldLoadBanner(): bool
    {
        return self::enabled()
            && self::siteId() !== null
            && AdSettings::isProductionEnvironment();
    }
}
