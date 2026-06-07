<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Yasal sayfa ve iletişim bilgileri
    |--------------------------------------------------------------------------
    |
    | Gizlilik, çerez, hakkımızda ve iletişim sayfalarında kullanılır.
    | .env ile geçersiz kılınabilir (LEGAL_*).
    |
    */

    'company_name' => env(
        'LEGAL_COMPANY_NAME',
        'GOAT Bilişim Teknolojileri Ticaret Anonim Şirketi'
    ),

    'company_short_name' => env(
        'LEGAL_COMPANY_SHORT_NAME',
        'GOAT Bilişim Teknolojileri Ticaret A.Ş.'
    ),

    'contact_email' => env(
        'LEGAL_CONTACT_EMAIL',
        'modapusula@goatbilisim.com'
    ),

    'company_address' => env(
        'LEGAL_COMPANY_ADDRESS',
        'Halkalı Merkez Mahallesi Fatih Caddesi, Serenity Plus Sitesi B Blok No: 61-63 İç Kapı No: 9, Küçükçekmece / İstanbul, Türkiye'
    ),

    'country' => env('LEGAL_COUNTRY', 'Türkiye'),

    'tax_office' => env('LEGAL_TAX_OFFICE', 'Halkalı Vergi Dairesi'),

    'tax_number' => env('LEGAL_TAX_NUMBER', '3961630836'),

    'mersis_number' => env('LEGAL_MERSIS_NUMBER', '0396160383600001'),

    'trade_registry_number' => env('LEGAL_TRADE_REGISTRY_NUMBER', '493286-5'),

    'authorized_person' => env('LEGAL_AUTHORIZED_PERSON', 'Onur Akpınar'),

];
