<?php

namespace App\Support\Ads;

use App\Enums\PageStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Support\PublicContent;
use Illuminate\Support\Facades\URL;

class AdSenseReadinessChecker
{
    /**
     * @return array<int, array{key: string, label: string, passed: bool, note: string|null}>
     */
    public function checks(): array
    {
        return [
            $this->check('site_name', 'Gerçek site adı girildi mi?', $this->hasSiteName()),
            $this->check('contact_email', 'İletişim e-posta adresi girildi mi?', $this->hasContactEmail()),
            $this->check('about_page', 'Hakkımızda sayfası yayınlandı mı?', $this->isPageReady('hakkimizda')),
            $this->check('contact_page', 'İletişim sayfası aktif mi?', $this->isContactPageReady()),
            $this->check('privacy_policy', 'Gizlilik politikası tamamlandı mı?', $this->isPrivacyReady()),
            $this->check('cookie_policy', 'Çerez politikası tamamlandı mı?', $this->isCookiePolicyReady()),
            $this->check('terms_page', 'Kullanım koşulları yayınlandı mı?', $this->isPageReady('kullanim-kosullari')),
            $this->check('editorial_page', 'Yayın ilkeleri yayınlandı mı?', $this->isEditorialReady()),
            $this->check('corrections_page', 'Düzeltme politikası yayınlandı mı?', $this->isPageReady('duzeltme-politikasi')),
            $this->check(
                'quality_posts',
                'En az 20 özgün ve insan kontrolünden geçmiş yazı yayınlandı mı?',
                $this->hasEnoughQualityPosts(),
                'Proje içi kalite eşiği; Google’ın resmi sayısal şartı değildir.'
            ),
            $this->check(
                'categories',
                'En az 4 aktif kategori var mı?',
                $this->hasEnoughCategories(),
                'Proje içi kalite eşiği; Google’ın resmi sayısal şartı değildir.'
            ),
            $this->check('authors', 'Gerçek yazar profili var mı?', $this->hasRealAuthors()),
            $this->check('empty_pages', 'Boş veya lorem ipsum sayfa yok mu?', ! $this->hasPlaceholderOrEmptyPublishedPages()),
            $this->check('sitemap', 'Sitemap erişilebilir mi?', $this->routeExists('sitemap')),
            $this->check('robots', 'robots.txt erişilebilir mi?', $this->routeExists('robots')),
            $this->check('ads_txt', 'ads.txt durumu nedir?', $this->adsTxtReady(), note: $this->adsTxtNote()),
            $this->check('cmp', 'CMP yapılandırıldı mı?', AdSettings::certifiedCmpConfigured()),
            $this->check('https', 'HTTPS kullanılıyor mu?', $this->usesHttps()),
        ];
    }

    public function allPassed(): bool
    {
        foreach ($this->checks() as $check) {
            if (! $check['passed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{key: string, label: string, passed: bool, note: string|null}
     */
    private function check(string $key, string $label, bool $passed, ?string $note = null, bool $invert = false): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'passed' => $invert ? ! $passed : $passed,
            'note' => $note,
        ];
    }

    private function hasSiteName(): bool
    {
        return trim((string) SiteSetting::get('site_name', '')) !== '';
    }

    private function hasContactEmail(): bool
    {
        return \App\Support\Legal\LegalPlaceholders::isValidEmail(
            \App\Support\Legal\LegalPlaceholders::effectiveContactEmail(SiteSetting::get('contact_email'))
        );
    }

    private function isPageReady(string $slug): bool
    {
        $page = Page::query()->where('slug', $slug)->first();

        return $page
            && $page->status === PageStatus::Published
            && PageTemplates::isPublicReady($page->body ?? '');
    }

    private function isContactPageReady(): bool
    {
        if (! AdSettings::contactInformationCompleted()) {
            return false;
        }

        return $this->isPageReady('iletisim') && $this->hasContactEmail();
    }

    private function isPrivacyReady(): bool
    {
        return AdSettings::privacyPolicyCompleted() && $this->isPageReady('gizlilik-politikasi');
    }

    private function isCookiePolicyReady(): bool
    {
        return AdSettings::cookiePolicyCompleted() && $this->isPageReady('cerez-politikasi');
    }

    private function isEditorialReady(): bool
    {
        return AdSettings::editorialInformationCompleted() && $this->isPageReady('yayin-ilkeleri');
    }

    private function hasEnoughQualityPosts(): bool
    {
        return Post::query()
            ->publiclyVisible()
            ->whereNotNull('originality_confirmed_at')
            ->whereNotNull('human_reviewed_at')
            ->count() >= 20;
    }

    private function hasEnoughCategories(): bool
    {
        return Category::query()
            ->active()
            ->withPublishedPosts()
            ->count() >= 4;
    }

    private function hasRealAuthors(): bool
    {
        return Author::query()
            ->active()
            ->whereHas('posts', fn ($query) => $query->publiclyVisible())
            ->whereNotNull('short_bio')
            ->where('short_bio', '!=', '')
            ->exists();
    }

    private function hasPlaceholderOrEmptyPublishedPages(): bool
    {
        foreach (array_values(PublicContent::staticPageRoutes()) as $slug) {
            $page = Page::query()->where('slug', $slug)->first();

            if (! $page || $page->status !== PageStatus::Published) {
                continue;
            }

            if (! PageTemplates::isPublicReady($page->body ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function routeExists(string $name): bool
    {
        return app('router')->has($name);
    }

    private function adsTxtReady(): bool
    {
        return AdSettings::publisherId() !== null;
    }

    private function adsTxtNote(): ?string
    {
        if (AdSettings::publisherId() !== null) {
            return 'Geçerli publisher ID ile ads.txt üretilebilir.';
        }

        return 'Publisher ID girilmeden sahte ads.txt satırı oluşturulmaz.';
    }

    private function usesHttps(): bool
    {
        return str_starts_with(URL::to('/'), 'https://');
    }
}
