<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AdSense doğrulama ve kimlik
    |--------------------------------------------------------------------------
    |
    | Canlı değerler öncelikle site_settings (admin panel) üzerinden okunur.
    | Ortam değişkenleri deploy sırasında adsense:sync-env ile DB'ye yazılabilir.
    |
    */

    'verification_enabled' => filter_var(env('ADSENSE_VERIFICATION_ENABLED', false), FILTER_VALIDATE_BOOL),

    'client_id' => env('ADSENSE_CLIENT_ID'),

    'publisher_id' => env('ADSENSE_PUBLISHER_ID'),

    'ads_enabled' => filter_var(env('ADSENSE_ADS_ENABLED', false), FILTER_VALIDATE_BOOL),

    'auto_ads_enabled' => filter_var(env('ADSENSE_AUTO_ADS_ENABLED', false), FILTER_VALIDATE_BOOL),

    'certified_cmp_configured' => filter_var(env('CERTIFIED_CMP_CONFIGURED', false), FILTER_VALIDATE_BOOL),

    'article_middle_slot' => env('ADSENSE_ARTICLE_MIDDLE_SLOT'),

    'article_bottom_slot' => env('ADSENSE_ARTICLE_BOTTOM_SLOT'),

];
