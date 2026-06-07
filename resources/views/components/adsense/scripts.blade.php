@php
    use App\Support\Ads\AdSettings;
    use App\Support\Consent\CookieYesSettings;
@endphp

@if (AdSettings::isLocalOrTestingEnvironment())
    {{-- Üretim dışı ortamda reklam scriptleri yüklenmez. --}}
@elseif (CookieYesSettings::defersAdScriptsUntilConsent() && AdSettings::shouldLoadVerificationScript())
    @once
        <script id="adsense-consent-config" type="application/json">
            {!! json_encode([
                'clientId' => AdSettings::clientId(),
                'adsEnabled' => AdSettings::adsEnabled(),
                'autoAds' => AdSettings::autoAdsEnabled()
                    && AdSettings::adsEnabled()
                    && AdSettings::certifiedCmpConfigured()
                    && AdSettings::isProductionEnvironment(),
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}
        </script>
        <script src="{{ asset('js/adsense-consent.js') }}" defer></script>
    @endonce
@elseif (AdSettings::shouldLoadVerificationScript())
    @once
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ AdSettings::clientId() }}" crossorigin="anonymous"></script>
    @endonce

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
