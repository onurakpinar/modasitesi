<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Adı
    |--------------------------------------------------------------------------
    |
    | Yayın sitesinin görünen adı. İleride admin panelinden güncellenebilir.
    | Şimdilik APP_NAME ortam değişkeninden okunur.
    |
    */

    'name' => env('APP_NAME', 'ModaPusula'),

    /*
    |--------------------------------------------------------------------------
    | Site Sloganı
    |--------------------------------------------------------------------------
    */

    'tagline' => 'Moda ve stil üzerine özgün yayınlar',

    /*
    |--------------------------------------------------------------------------
    | Yayıncı (Schema.org Organization / Article publisher)
    |--------------------------------------------------------------------------
    */

    'publisher_legal_name' => env('SITE_PUBLISHER_LEGAL_NAME', 'GOAT Bilişim Teknolojileri Ticaret A.Ş.'),

];
