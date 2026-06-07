@php
    use App\Support\Ads\AdSettings;
    use App\Support\Consent\CookieYesSettings;
@endphp

@if (AdSettings::isLocalOrTestingEnvironment())
    {{-- Üretim dışı ortamda reklam scriptleri yüklenmez. --}}
@else
    @if (AdSettings::shouldLoadVerificationScript())
        @once
            @if (CookieYesSettings::defersAdScriptsUntilConsent())
                <script
                    type="text/plain"
                    data-cookieyes="{{ CookieYesSettings::ADVERTISEMENT_CATEGORY }}"
                    async
                    src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ AdSettings::clientId() }}"
                    crossorigin="anonymous"
                ></script>
            @else
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ AdSettings::clientId() }}" crossorigin="anonymous"></script>
            @endif
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
            @if (CookieYesSettings::defersAdScriptsUntilConsent())
                <script type="text/plain" data-cookieyes="{{ CookieYesSettings::ADVERTISEMENT_CATEGORY }}">
                    (adsbygoogle = window.adsbygoogle || []).push({ google_ad_client: "{{ AdSettings::clientId() }}", enable_page_level_ads: true });
                </script>
            @else
                <script>(adsbygoogle = window.adsbygoogle || []).push({ google_ad_client: "{{ AdSettings::clientId() }}", enable_page_level_ads: true });</script>
            @endif
        @endonce
    @endif
@endif
