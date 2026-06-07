@props(['post'])

@php
    use App\Support\Ads\AdEligibility;
    use App\Support\Ads\AdSettings;
    use App\Support\Consent\CookieYesSettings;
@endphp

@if (AdEligibility::canShowMiddleSlot($post))
    <aside class="ad-slot my-12 max-w-full overflow-hidden" aria-label="Advertisement">
        <p class="ad-slot__label mb-3 text-xs uppercase tracking-[0.2em] text-stone-400">Advertisement</p>
        <ins
            class="adsbygoogle block max-w-full overflow-hidden"
            style="display:block"
            data-ad-client="{{ AdSettings::clientId() }}"
            data-ad-slot="{{ AdSettings::articleMiddleSlot() }}"
            data-ad-format="auto"
            data-full-width-responsive="true"
        ></ins>
        @if (CookieYesSettings::defersAdScriptsUntilConsent())
            <script type="text/plain" data-cookieyes="{{ CookieYesSettings::ADVERTISEMENT_CATEGORY }}">(adsbygoogle = window.adsbygoogle || []).push({});</script>
        @else
            <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
        @endif
    </aside>
@endif
