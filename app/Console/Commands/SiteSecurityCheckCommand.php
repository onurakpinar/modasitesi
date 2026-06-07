<?php

namespace App\Console\Commands;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Support\Ads\AdSettings;
use App\Support\Ads\AdSenseReadinessChecker;
use App\Support\Ads\PageTemplates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Route as RouterRoute;

class SiteSecurityCheckCommand extends Command
{
    protected $signature = 'site:security-check';

    protected $description = 'Üretim güvenliği, SEO ve AdSense hazırlık kontrollerini çalıştırır';

    public function handle(AdSenseReadinessChecker $readiness): int
    {
        $production = config('app.env') === 'production';

        $checks = [
            $this->check('APP_ENV', config('app.env'), config('app.env') === 'production', 'Üretimde production olmalı', $production),
            $this->check('APP_DEBUG', config('app.debug') ? 'true' : 'false', config('app.debug') === false, 'Üretimde false olmalı', $production),
            $this->check('APP_URL HTTPS', config('app.url'), str_starts_with((string) config('app.url'), 'https://'), 'HTTPS kullanılmalı', $production),
            $this->check('Session secure cookie', config('session.secure') ? 'true' : 'false', (bool) config('session.secure'), 'SESSION_SECURE_COOKIE=true önerilir', $production),
            $this->check('Gizlilik sayfası', $this->privacyStatus(), $this->privacyReady(), 'Yayınlanmış ve eksiksiz olmalı', $production),
            $this->check(
                'CMP yapılandırması',
                AdSettings::certifiedCmpConfigured() ? 'evet' : 'hayır',
                ! AdSettings::adsEnabled() || AdSettings::certifiedCmpConfigured(),
                'Reklamlar açıkken sertifikalı CMP gerekli'
            ),
            $this->check('AdSense doğrulama', AdSettings::verificationEnabled() ? 'açık' : 'kapalı', true, 'Bilgi amaçlı', false),
            $this->check('AdSense reklamlar', AdSettings::adsEnabled() ? 'açık' : 'kapalı', ! AdSettings::adsEnabled() || AdSettings::certifiedCmpConfigured(), 'CMP olmadan reklam açık olmamalı'),
            $this->check('Storage yazılabilir', 'public disk', Storage::disk('public')->path('') !== '' && is_writable(Storage::disk('public')->path('')), 'storage/app/public yazılabilir olmalı'),
            $this->check('İletişim rate limit', 'contact-form', $this->routeUsesThrottle('contact.store', 'contact-form'), 'throttle:contact-form tanımlı olmalı'),
            $this->check('Arama rate limit', 'search', $this->routeUsesThrottle('search', 'search'), 'throttle:search tanımlı olmalı'),
            $this->check('İletişim CSRF', 'web middleware', $this->routeUsesMiddleware('contact.store', 'web'), 'web grubunda CSRF koruması olmalı'),
            $this->check('Health bilgi sızdırması', $this->healthLeakStatus(), ! $this->healthLeaksEnvironment(), 'environment alanı dönmemeli'),
            $this->check('Sitemap route', 'sitemap', Route::has('sitemap'), 'Route tanımlı olmalı'),
            $this->check('robots.txt route', 'robots', Route::has('robots'), 'Route tanımlı olmalı'),
            $this->check('ads.txt route', 'ads.txt', Route::has('ads.txt'), 'Route tanımlı olmalı'),
            $this->check('Admin login route', 'admin.login', Route::has('admin.login'), 'Route tanımlı olmalı'),
            $this->check('Admin login rate limit', 'admin-login', RateLimiter::limiter('admin-login') !== null, 'admin-login limiter tanımlı olmalı'),
        ];

        $failed = 0;

        foreach ($checks as $item) {
            $status = $item['ok'] ? '<fg=green>GEÇTI</>' : '<fg=red>UYARI</>';
            $this->line(sprintf('[%s] %s — %s', $status, $item['label'], $item['detail']));

            if (! $item['ok'] && $item['required']) {
                $failed++;
            }
        }

        $readinessFailed = collect($readiness->checks())->where('passed', false)->count();
        $this->newLine();
        $this->info("AdSense hazırlık kontrolü: {$readinessFailed} eksik madde.");

        if ($failed > 0) {
            $this->error("{$failed} kritik kontrol başarısız.");

            return self::FAILURE;
        }

        if (! $production && $checks !== []) {
            $this->comment('Yerel/test ortamı: üretim kontrolleri bilgi amaçlı gösterildi.');
        }

        $this->info('Temel güvenlik kontrolleri tamamlandı.');

        return self::SUCCESS;
    }

    /**
     * @return array{label: string, detail: string, ok: bool, required: bool}
     */
    private function check(string $label, string $value, bool $ok, string $detail, bool $required = true): array
    {
        return [
            'label' => $label,
            'detail' => $value.' — '.$detail,
            'ok' => $ok,
            'required' => $required,
        ];
    }

    private function privacyStatus(): string
    {
        $page = Page::query()->where('slug', 'gizlilik-politikasi')->first();

        if (! $page) {
            return 'sayfa yok';
        }

        return $page->status->value;
    }

    private function privacyReady(): bool
    {
        if (! AdSettings::privacyPolicyCompleted()) {
            return false;
        }

        $page = Page::query()
            ->where('slug', 'gizlilik-politikasi')
            ->where('status', PageStatus::Published)
            ->first();

        return $page && PageTemplates::isPublicReady($page->body ?? '');
    }

    private function routeUsesThrottle(string $routeName, string $limiter): bool
    {
        $route = Route::getRoutes()->getByName($routeName);

        if (! $route instanceof RouterRoute) {
            return false;
        }

        return in_array('throttle:'.$limiter, $route->gatherMiddleware(), true);
    }

    private function routeUsesMiddleware(string $routeName, string $middleware): bool
    {
        $route = Route::getRoutes()->getByName($routeName);

        if (! $route instanceof RouterRoute) {
            return false;
        }

        return in_array($middleware, $route->gatherMiddleware(), true);
    }

    private function healthLeaksEnvironment(): bool
    {
        $response = app(\App\Http\Controllers\HealthController::class)();

        if (! method_exists($response, 'getData')) {
            return true;
        }

        $payload = (array) $response->getData(true);

        return array_key_exists('environment', $payload);
    }

    private function healthLeakStatus(): string
    {
        return $this->healthLeaksEnvironment() ? 'environment alanı var' : 'temiz';
    }
}
