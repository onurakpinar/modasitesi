@php
    use App\Support\Ads\AdSettings;
@endphp

@if (AdSettings::isLocalOrTestingEnvironment())
    <!-- AdSense doğrulama scripti yerel/test ortamında yüklenmez. -->
@else
    @if (AdSettings::shouldLoadVerificationScript())
        @once
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ AdSettings::clientId() }}" crossorigin="anonymous"></script>
        @endonce
    @endif

    @if (
        AdSettings::autoAdsEnabled()
        && AdSettings::adsEnabled()
        && AdSettings::certifiedCmpConfigured()
        && AdSettings::clientId()
        && AdSettings::isProductionEnvironment()
    )
        @once
            <script>(adsbygoogle = window.adsbygoogle || []).push({ google_ad_client: "{{ AdSettings::clientId() }}", enable_page_level_ads: true });</script>
        @endonce
    @endif
@endif
