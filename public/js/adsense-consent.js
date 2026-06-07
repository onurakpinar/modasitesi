(function () {
    'use strict';

    var configElement = document.getElementById('adsense-consent-config');

    if (!configElement) {
        return;
    }

    var config;

    try {
        config = JSON.parse(configElement.textContent || '{}');
    } catch (error) {
        return;
    }

    if (!config.clientId) {
        return;
    }

    var scriptRequested = false;
    var scriptLoaded = false;

    function userCompletedConsent() {
        if (typeof window.getCkyConsent !== 'function') {
            return false;
        }

        var consent = window.getCkyConsent();

        return Boolean(consent && consent.isUserActionCompleted);
    }

    function advertisementAccepted(detail) {
        if (detail && Array.isArray(detail.accepted)) {
            return detail.accepted.indexOf('advertisement') !== -1;
        }

        if (typeof window.getCkyConsent !== 'function') {
            return false;
        }

        var consent = window.getCkyConsent();

        return Boolean(consent && consent.categories && consent.categories.advertisement === 'yes');
    }

    function pushAdSlots() {
        var slots = document.querySelectorAll('ins.adsbygoogle');

        if (!slots.length) {
            return;
        }

        window.adsbygoogle = window.adsbygoogle || [];

        slots.forEach(function () {
            window.adsbygoogle.push({});
        });
    }

    function loadAdSenseScript() {
        if (scriptLoaded) {
            pushAdSlots();

            return;
        }

        if (scriptRequested) {
            return;
        }

        scriptRequested = true;

        var script = document.createElement('script');
        script.async = true;
        script.crossOrigin = 'anonymous';
        script.dataset.adsenseLoader = '1';
        script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent(config.clientId);
        script.onload = function () {
            scriptLoaded = true;

            if (config.autoAds && config.adsEnabled) {
                window.adsbygoogle = window.adsbygoogle || [];
                window.adsbygoogle.push({
                    google_ad_client: config.clientId,
                    enable_page_level_ads: true,
                });
            }

            pushAdSlots();
        };

        document.head.appendChild(script);
    }

    function maybeLoadAds(detail) {
        if (!userCompletedConsent() || !advertisementAccepted(detail)) {
            return;
        }

        loadAdSenseScript();
    }

    document.addEventListener('cookieyes_consent_update', function (event) {
        maybeLoadAds(event.detail || {});
    });

    document.addEventListener('cookieyes_banner_load', function () {
        if (!userCompletedConsent() || !advertisementAccepted()) {
            return;
        }

        loadAdSenseScript();
    });
})();
