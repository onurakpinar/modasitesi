<?php

namespace App\Http\Middleware;

use App\Support\Ads\AdSettings;
use App\Support\Consent\CookieYesSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->isSecure() || str_starts_with((string) config('app.url'), 'https://')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $adsenseHosts = implode(' ', [
            'https://pagead2.googlesyndication.com',
            'https://www.googletagservices.com',
            'https://www.google.com',
            'https://googleads.g.doubleclick.net',
            'https://tpc.googlesyndication.com',
            'https://partner.googleadservices.com',
            'https://www.gstatic.com',
        ]);

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "img-src 'self' data: https:",
            "font-src 'self'",
            "style-src 'self' 'unsafe-inline'",
            "script-src 'self' 'unsafe-inline'",
            "connect-src 'self'",
        ];

        $scriptHosts = [];
        $connectHosts = [];

        if ($this->allowsCookieYesScripts()) {
            $cookieYesHosts = implode(' ', [
                'https://cdn-cookieyes.com',
                'https://log.cookieyes.com',
            ]);
            $scriptHosts[] = $cookieYesHosts;
            $connectHosts[] = $cookieYesHosts;
        }

        if ($this->allowsAdSenseScripts()) {
            $scriptHosts[] = $adsenseHosts;
            $connectHosts[] = $adsenseHosts;
            $directives[] = "frame-src 'self' https://googleads.g.doubleclick.net https://tpc.googlesyndication.com https://www.google.com";
        }

        if ($scriptHosts !== []) {
            $directives[7] = "script-src 'self' 'unsafe-inline' ".implode(' ', $scriptHosts);
        }

        if ($connectHosts !== []) {
            $directives[8] = "connect-src 'self' ".implode(' ', $connectHosts);
        }

        return implode('; ', $directives).';';
    }

    private function allowsCookieYesScripts(): bool
    {
        if (AdSettings::isLocalOrTestingEnvironment()) {
            return false;
        }

        return CookieYesSettings::enabled() && CookieYesSettings::siteId() !== null;
    }

    private function allowsAdSenseScripts(): bool
    {
        if (AdSettings::isLocalOrTestingEnvironment()) {
            return false;
        }

        return AdSettings::shouldLoadVerificationScript()
            || (AdSettings::adsEnabled() && AdSettings::certifiedCmpConfigured() && AdSettings::clientId() !== null);
    }
}
