<?php

namespace App\Support\Ads;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class AdSettings
{
    private static ?string $simulatedEnvironment = null;

    public static function simulateEnvironment(?string $environment): void
    {
        self::$simulatedEnvironment = $environment;
    }

    public static function resetSimulation(): void
    {
        self::$simulatedEnvironment = null;
    }

    /**
     * @deprecated Use simulateEnvironment('production') instead.
     */
    public static function simulateProduction(bool $value = true): void
    {
        self::$simulatedEnvironment = $value ? 'production' : null;
    }

    public static function verificationEnabled(): bool
    {
        return self::boolean('adsense_verification_enabled', false);
    }

    public static function adsEnabled(): bool
    {
        return self::boolean('adsense_ads_enabled', false);
    }

    public static function autoAdsEnabled(): bool
    {
        return self::boolean('adsense_auto_ads_enabled', false);
    }

    public static function clientId(): ?string
    {
        $value = self::get('adsense_client_id');

        return AdSenseValidator::isValidClientId($value) ? $value : null;
    }

    public static function publisherId(): ?string
    {
        $value = self::get('adsense_publisher_id');

        return AdSenseValidator::isValidPublisherId($value) ? $value : null;
    }

    public static function articleMiddleSlot(): ?string
    {
        $value = self::get('adsense_article_middle_slot');

        return AdSenseValidator::isValidSlotId($value) ? $value : null;
    }

    public static function articleBottomSlot(): ?string
    {
        $value = self::get('adsense_article_bottom_slot');

        return AdSenseValidator::isValidSlotId($value) ? $value : null;
    }

    public static function certifiedCmpConfigured(): bool
    {
        return self::boolean('certified_cmp_configured', false);
    }

    public static function privacyPolicyCompleted(): bool
    {
        return self::boolean('privacy_policy_completed', false);
    }

    public static function cookiePolicyCompleted(): bool
    {
        return self::boolean('cookie_policy_completed', false);
    }

    public static function contactInformationCompleted(): bool
    {
        return self::boolean('contact_information_completed', false);
    }

    public static function editorialInformationCompleted(): bool
    {
        return self::boolean('editorial_information_completed', false);
    }

    public static function shouldLoadVerificationScript(): bool
    {
        return self::verificationEnabled()
            && self::clientId() !== null
            && self::isProductionEnvironment();
    }

    public static function isProductionEnvironment(): bool
    {
        return self::currentEnvironment() === 'production';
    }

    public static function isLocalOrTestingEnvironment(): bool
    {
        return in_array(self::currentEnvironment(), ['local', 'testing'], true);
    }

    public static function currentEnvironment(): string
    {
        if (self::$simulatedEnvironment !== null) {
            return self::$simulatedEnvironment;
        }

        return app()->environment();
    }

    /**
     * @return array<string, bool|string|null>
     */
    public static function allForAdmin(): array
    {
        return [
            'adsense_verification_enabled' => self::verificationEnabled(),
            'adsense_ads_enabled' => self::adsEnabled(),
            'adsense_auto_ads_enabled' => self::autoAdsEnabled(),
            'adsense_client_id' => self::get('adsense_client_id'),
            'adsense_publisher_id' => self::get('adsense_publisher_id'),
            'adsense_article_middle_slot' => self::get('adsense_article_middle_slot'),
            'adsense_article_bottom_slot' => self::get('adsense_article_bottom_slot'),
            'certified_cmp_configured' => self::certifiedCmpConfigured(),
            'privacy_policy_completed' => self::privacyPolicyCompleted(),
            'cookie_policy_completed' => self::cookiePolicyCompleted(),
            'contact_information_completed' => self::contactInformationCompleted(),
            'editorial_information_completed' => self::editorialInformationCompleted(),
        ];
    }

    public static function setBoolean(string $key, bool $value, string $group = 'adsense'): void
    {
        SiteSetting::set($key, $value ? '1' : '0', $group);
    }

    public static function setString(string $key, ?string $value, string $group = 'adsense'): void
    {
        SiteSetting::set($key, $value ?? '', $group);
    }

    private static function boolean(string $key, bool $default = false): bool
    {
        $value = self::getFromDatabase($key);

        if ($value !== null) {
            return in_array($value, ['1', 'true', 'on', 'yes'], true);
        }

        return self::configBoolean($key, $default);
    }

    private static function get(string $key, ?string $default = null): ?string
    {
        $value = self::getFromDatabase($key);

        if ($value !== null) {
            return $value;
        }

        $configured = self::configString($key);

        return $configured ?? $default;
    }

    private static function getFromDatabase(string $key): ?string
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $value = SiteSetting::get($key);

        return filled($value) ? $value : null;
    }

    private static function configString(string $key): ?string
    {
        return match ($key) {
            'adsense_client_id' => config('adsense.client_id'),
            'adsense_publisher_id' => config('adsense.publisher_id'),
            default => null,
        };
    }

    private static function configBoolean(string $key, bool $default): bool
    {
        return match ($key) {
            'adsense_verification_enabled' => (bool) config('adsense.verification_enabled', $default),
            'adsense_ads_enabled' => (bool) config('adsense.ads_enabled', $default),
            'adsense_auto_ads_enabled' => (bool) config('adsense.auto_ads_enabled', $default),
            default => $default,
        };
    }
}
