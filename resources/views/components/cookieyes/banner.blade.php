@php
    use App\Support\Consent\CookieYesSettings;
@endphp

@if (CookieYesSettings::shouldLoadBanner())
    <!-- Start cookieyes banner -->
    <script id="cookieyes" type="text/javascript" src="{{ CookieYesSettings::scriptUrl() }}"></script>
    <!-- End cookieyes banner -->
@endif
