<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CookieYes CMP
    |--------------------------------------------------------------------------
    |
    | Google sertifikalı çerez onay banner'ı. Script yalnızca üretimde yüklenir.
    |
    */

    'enabled' => filter_var(env('COOKIEYES_ENABLED', false), FILTER_VALIDATE_BOOL),

    'site_id' => env('COOKIEYES_SITE_ID'),

];
