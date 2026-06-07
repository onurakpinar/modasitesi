<?php

namespace App\Support\Consent;

use App\Support\Ads\AdSettings;

class CookieYesSettings
{
    public const ADVERTISEMENT_CATEGORY = 'cookieyes-advertisement';

    public static function defersAdScriptsUntilConsent(): bool
    {
        return AdSettings::isProductionEnvironment();
    }
}
