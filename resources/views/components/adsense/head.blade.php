@php
    use App\Support\Ads\AdSettings;
@endphp

@if (AdSettings::isLocalOrTestingEnvironment())
    <!-- AdSense doğrulama scripti yerel/test ortamında yüklenmez. -->
@elseif (AdSettings::shouldLoadVerificationScript())
    <meta name="google-adsense-account" content="{{ AdSettings::clientId() }}">
@endif
