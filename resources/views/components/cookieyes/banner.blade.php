@php
    use App\Support\Consent\CookieYesSettings;
@endphp

@if (CookieYesSettings::shouldLoadBanner())
    <script id="cookieyes" type="text/javascript" src="{{ CookieYesSettings::scriptUrl() }}"></script>
@endif
