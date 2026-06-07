<?php

namespace App\Http\Middleware;

use App\Support\Ads\AdSettings;
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

        if ($this->allowsAdSenseScripts()) {
            $directives[7] = "script-src 'self' 'unsafe-inline' {$adsenseHosts}";
            $directives[8] = "connect-src 'self' {$adsenseHosts}";
            $directives[] = "frame-src 'self' https://googleads.g.doubleclick.net https://tpc.googlesyndication.com https://www.google.com";
        }

        return implode('; ', $directives).';';
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
