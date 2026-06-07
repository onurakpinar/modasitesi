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
        $directives = [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'object-src' => ["'none'"],
            'frame-ancestors' => ["'self'"],
            'img-src' => ["'self'", 'data:', 'https:'],
            'font-src' => ["'self'"],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'script-src' => ["'self'", "'unsafe-inline'"],
            'connect-src' => ["'self'"],
        ];

        if ($this->allowsCookieYesScripts()) {
            $cookieYesHosts = $this->cookieYesHosts();

            $this->mergeDirectiveSources($directives, 'script-src', $cookieYesHosts);
            $this->mergeDirectiveSources($directives, 'connect-src', $cookieYesHosts);
            $this->mergeDirectiveSources($directives, 'style-src', $cookieYesHosts);
            $this->mergeDirectiveSources($directives, 'font-src', $cookieYesHosts);
            $this->mergeDirectiveSources($directives, 'frame-src', array_merge(["'self'"], $cookieYesHosts));
        }

        if ($this->allowsAdSenseScripts()) {
            $adsenseHosts = $this->adSenseHosts();

            $this->mergeDirectiveSources($directives, 'script-src', $adsenseHosts);
            $this->mergeDirectiveSources($directives, 'connect-src', $adsenseHosts);

            $frameHosts = [
                'https://googleads.g.doubleclick.net',
                'https://tpc.googlesyndication.com',
                'https://www.google.com',
            ];

            if (isset($directives['frame-src'])) {
                $this->mergeDirectiveSources($directives, 'frame-src', $frameHosts);
            } else {
                $directives['frame-src'] = array_merge(["'self'"], $frameHosts);
            }
        }

        $parts = [];

        foreach ($directives as $name => $sources) {
            $parts[] = $name.' '.implode(' ', array_unique($sources));
        }

        return implode('; ', $parts).';';
    }

    /**
     * @param  array<string, array<int, string>>  $directives
     * @param  array<int, string>  $sources
     */
    private function mergeDirectiveSources(array &$directives, string $directive, array $sources): void
    {
        $directives[$directive] = array_merge($directives[$directive] ?? [], $sources);
    }

    /**
     * @return array<int, string>
     */
    private function cookieYesHosts(): array
    {
        // cdn-cookieyes.com is its own registrable domain; *.cookieyes.com does not match it.
        return [
            'https://cdn-cookieyes.com',
            'https://log.cookieyes.com',
            'https://directory.cookieyes.com',
            'https://*.cookieyes.com',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function adSenseHosts(): array
    {
        return [
            'https://pagead2.googlesyndication.com',
            'https://www.googletagservices.com',
            'https://www.google.com',
            'https://googleads.g.doubleclick.net',
            'https://tpc.googlesyndication.com',
            'https://partner.googleadservices.com',
            'https://www.gstatic.com',
        ];
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
