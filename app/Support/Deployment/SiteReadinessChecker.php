<?php

namespace App\Support\Deployment;

use App\Enums\PageStatus;
use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Ads\AdSettings;
use App\Support\Ads\PageTemplates;
use App\Support\PostQualityChecker;
use App\Support\PublicContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class SiteReadinessChecker
{
    /**
     * @return array<int, array{label: string, status: ReadinessStatus, detail: string}>
     */
    public function checks(): array
    {
        return [
            $this->environmentCheck('APP_ENV production', config('app.env') === 'production', config('app.env'), 'Üretimde APP_ENV=production olmalı.'),
            $this->environmentCheck('APP_DEBUG kapalı', config('app.debug') === false, config('app.debug') ? 'true' : 'false', 'Üretimde APP_DEBUG=false olmalı.'),
            $this->environmentCheck('APP_URL HTTPS', str_starts_with((string) config('app.url'), 'https://'), config('app.url'), 'APP_URL https:// ile başlamalı.'),
            $this->result('Veritabanı erişimi', $this->databaseAccessible() ? ReadinessStatus::Pass : ReadinessStatus::Fail, $this->databaseAccessible() ? 'Bağlantı başarılı.' : 'Veritabanına bağlanılamıyor.'),
            $this->result('Storage yazılabilir', $this->storageWritable() ? ReadinessStatus::Pass : ReadinessStatus::Fail, $this->storageWritable() ? 'storage/app/public yazılabilir.' : 'storage dizini yazılamıyor.'),
            $this->result('Süper yönetici', $this->hasSuperAdmin() ? ReadinessStatus::Pass : ReadinessStatus::Fail, $this->hasSuperAdmin() ? 'En az bir aktif süper yönetici var.' : 'php artisan admin:create ile süper yönetici oluşturun.'),
            $this->result('Gerçek site adı', $this->hasSiteName() ? ReadinessStatus::Pass : ReadinessStatus::Fail, $this->hasSiteName() ? SiteSetting::get('site_name') : 'Admin panelinden site adı girilmedi.'),
            $this->result('Logo veya metin marka', $this->hasBranding() ? ReadinessStatus::Pass : ReadinessStatus::Warning, $this->brandingDetail()),
            $this->result('İletişim e-postası', $this->hasContactEmail() ? ReadinessStatus::Pass : ReadinessStatus::Fail, $this->hasContactEmail() ? SiteSetting::get('contact_email') : 'Geçerli iletişim e-postası girilmedi.'),
            $this->policyPagesCheck(),
            $this->result('Lorem ipsum kontrolü', ! $this->hasPublicLoremIpsum() ? ReadinessStatus::Pass : ReadinessStatus::Fail, $this->hasPublicLoremIpsum() ? 'Yayında lorem ipsum içerik bulundu.' : 'Yayın içeriklerinde lorem ipsum yok.'),
            $this->taslakSizintisiCheck(),
            $this->qualityPostsCheck(),
            $this->categoriesCheck(),
            $this->result('Gerçek yazar profili', $this->hasRealAuthor() ? ReadinessStatus::Pass : ReadinessStatus::Fail, $this->hasRealAuthor() ? 'Aktif yazar ve biyografi mevcut.' : 'Yayınlı yazısı olan aktif yazar profili gerekli.'),
            $this->routeCheck('Sitemap erişilebilir', 'sitemap'),
            $this->routeCheck('robots.txt erişilebilir', 'robots'),
            $this->adsTxtCheck(),
            $this->cmpCheck(),
            $this->adsEnabledCheck(),
        ];
    }

    public function hasFailures(): bool
    {
        foreach ($this->checks() as $check) {
            if ($check['status'] === ReadinessStatus::Fail) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function environmentCheck(string $label, bool $passed, string $value, string $failDetail): array
    {
        if ($passed) {
            return $this->result($label, ReadinessStatus::Pass, $value);
        }

        if (in_array(config('app.env'), ['local', 'testing'], true)) {
            return $this->result($label, ReadinessStatus::Warning, $value.' — '.$failDetail.' (yerel ortam)');
        }

        return $this->result($label, ReadinessStatus::Fail, $value.' — '.$failDetail);
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function result(string $label, ReadinessStatus $status, string $detail): array
    {
        return [
            'label' => $label,
            'status' => $status,
            'detail' => $detail,
        ];
    }

    private function databaseAccessible(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function storageWritable(): bool
    {
        $path = Storage::disk('public')->path('');

        return $path !== '' && is_dir($path) && is_writable($path);
    }

    private function hasSuperAdmin(): bool
    {
        return User::query()
            ->where('role', UserRole::SuperAdmin)
            ->where('is_active', true)
            ->exists();
    }

    private function hasSiteName(): bool
    {
        return trim((string) SiteSetting::get('site_name', '')) !== '';
    }

    private function hasBranding(): bool
    {
        return filled(SiteSetting::get('site_logo')) || $this->hasSiteName();
    }

    private function brandingDetail(): string
    {
        if (filled(SiteSetting::get('site_logo'))) {
            return 'Logo yüklendi.';
        }

        if ($this->hasSiteName()) {
            return 'Metin logo (site adı) kullanılıyor.';
        }

        return 'Logo veya site adı tanımlanmadı.';
    }

    private function hasContactEmail(): bool
    {
        return filter_var(SiteSetting::get('contact_email'), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function policyPagesCheck(): array
    {
        $missing = [];

        foreach (array_values(PublicContent::staticPageRoutes()) as $slug) {
            $page = Page::query()->where('slug', $slug)->first();

            if (! $page || $page->status !== PageStatus::Published || ! PageTemplates::isPublicReady($page->body ?? '')) {
                $missing[] = $slug;
            }
        }

        if ($missing === []) {
            return $this->result('Sabit politika sayfaları', ReadinessStatus::Pass, 'Tüm zorunlu sayfalar yayında.');
        }

        return $this->result(
            'Sabit politika sayfaları',
            ReadinessStatus::Fail,
            'Eksik veya taslak sayfalar: '.implode(', ', $missing)
        );
    }

    private function hasPublicLoremIpsum(): bool
    {
        foreach (Post::query()->publiclyVisible()->get(['body']) as $post) {
            if (PageTemplates::containsLoremIpsum($post->body ?? '')) {
                return true;
            }
        }

        foreach (array_values(PublicContent::staticPageRoutes()) as $slug) {
            $page = Page::query()->where('slug', $slug)->where('status', PageStatus::Published)->first();

            if ($page && PageTemplates::containsLoremIpsum($page->body ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function hasDraftLeakRisk(): bool
    {
        if (Post::query()->publiclyVisible()->whereNull('human_reviewed_at')->exists()) {
            return true;
        }

        if (Post::query()->publiclyVisible()->whereNull('originality_confirmed_at')->exists()) {
            return true;
        }

        return Post::query()
            ->where('status', PostStatus::Scheduled)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now()->subHour())
            ->exists();
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function taslakSizintisiCheck(): array
    {
        if (! $this->hasDraftLeakRisk()) {
            return $this->result(
                'Taslak sızıntısı',
                ReadinessStatus::Pass,
                'Yayında taslak veya onaysız içerik tespit edilmedi.'
            );
        }

        return $this->result('Taslak sızıntısı', ReadinessStatus::Warning, $this->draftLeakDetail());
    }

    private function draftLeakDetail(): string
    {
        if (Post::query()->publiclyVisible()->whereNull('human_reviewed_at')->exists()) {
            return 'İnsan kontrolü olmadan yayında yazı var.';
        }

        if (Post::query()->publiclyVisible()->whereNull('originality_confirmed_at')->exists()) {
            return 'Özgünlük onayı olmadan yayında yazı var.';
        }

        return 'Gecikmiş zamanlanmış yazılar var; scheduler çalıştırın.';
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function qualityPostsCheck(): array
    {
        $count = Post::query()
            ->publiclyVisible()
            ->whereNotNull('originality_confirmed_at')
            ->whereNotNull('human_reviewed_at')
            ->get()
            ->filter(fn (Post $post) => app(PostQualityChecker::class)->isPublishable($post))
            ->count();

        $note = 'Proje içi kalite eşiği; Google\'ın resmi sayısal şartı değildir.';

        if ($count >= 20) {
            return $this->result('Kaliteli yayın sayısı', ReadinessStatus::Pass, "{$count} yazı — {$note}");
        }

        return $this->result('Kaliteli yayın sayısı', ReadinessStatus::Warning, "{$count}/20 yazı — {$note}");
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function categoriesCheck(): array
    {
        $count = Category::query()->active()->withPublishedPosts()->count();
        $note = 'Proje içi kalite eşiği; Google\'ın resmi sayısal şartı değildir.';

        if ($count >= 4) {
            return $this->result('Aktif kategoriler', ReadinessStatus::Pass, "{$count} kategori — {$note}");
        }

        return $this->result('Aktif kategoriler', ReadinessStatus::Warning, "{$count}/4 kategori — {$note}");
    }

    private function hasRealAuthor(): bool
    {
        return Author::query()
            ->active()
            ->whereHas('posts', fn ($query) => $query->publiclyVisible())
            ->whereNotNull('short_bio')
            ->where('short_bio', '!=', '')
            ->exists();
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function routeCheck(string $label, string $routeName): array
    {
        return Route::has($routeName)
            ? $this->result($label, ReadinessStatus::Pass, "Route tanımlı: {$routeName}")
            : $this->result($label, ReadinessStatus::Fail, "Route eksik: {$routeName}");
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function adsTxtCheck(): array
    {
        if (! Route::has('ads.txt')) {
            return $this->result('ads.txt durumu', ReadinessStatus::Fail, 'ads.txt route tanımlı değil.');
        }

        if (AdSettings::publisherId() !== null) {
            return $this->result('ads.txt durumu', ReadinessStatus::Pass, 'Publisher ID ile ads.txt üretilebilir.');
        }

        return $this->result(
            'ads.txt durumu',
            ReadinessStatus::Warning,
            'Publisher ID girilmedi; sahte satır oluşturulmaz, yorum satırı döner.'
        );
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function cmpCheck(): array
    {
        if (AdSettings::adsEnabled() && ! AdSettings::certifiedCmpConfigured()) {
            return $this->result('CMP durumu', ReadinessStatus::Fail, 'Reklamlar açık ancak sertifikalı CMP onaylanmamış.');
        }

        if (AdSettings::certifiedCmpConfigured()) {
            return $this->result('CMP durumu', ReadinessStatus::Pass, 'Sertifikalı CMP yapılandırıldı olarak işaretlendi.');
        }

        return $this->result('CMP durumu', ReadinessStatus::Warning, 'CMP henüz yapılandırılmadı; reklamlar kapalıyken normal.');
    }

    /**
     * @return array{label: string, status: ReadinessStatus, detail: string}
     */
    private function adsEnabledCheck(): array
    {
        if (AdSettings::adsEnabled()) {
            return $this->result(
                'Reklam gösterimi',
                ReadinessStatus::Fail,
                'Reklam kutuları açık; AdSense onayı ve CMP doğrulamasından önce kapalı tutulmalı.'
            );
        }

        return $this->result('Reklam gösterimi', ReadinessStatus::Pass, 'Reklam kutuları kapalı (başvuru öncesi önerilen durum).');
    }
}
