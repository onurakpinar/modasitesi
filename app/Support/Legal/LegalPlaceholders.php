<?php

namespace App\Support\Legal;

class LegalPlaceholders
{
    /**
     * @var list<string>
     */
    public const ALLOWED_IN_PUBLIC_BODY = [
        '[ŞİRKET_ADI]',
        '[E-POSTA_ADRESİ]',
        '[ŞİRKET_ADRESİ]',
        '[GUNCELLEME_TARIHI]',
    ];

    public static function companyName(): string
    {
        return (string) config('legal.company_name');
    }

    public static function contactEmail(): string
    {
        return (string) config('legal.contact_email');
    }

    public static function companyAddress(): string
    {
        return (string) config('legal.company_address');
    }

    public static function isValidEmail(?string $email): bool
    {
        return filled($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function effectiveContactEmail(?string $siteSettingEmail = null): ?string
    {
        if (self::isValidEmail($siteSettingEmail)) {
            return $siteSettingEmail;
        }

        $legalEmail = self::contactEmail();

        return filled($legalEmail) ? $legalEmail : null;
    }

    public static function render(string $body): string
    {
        return str_replace(
            [
                '[GUNCELLEME_TARIHI]',
                '[ŞİRKET_ADI]',
                '[E-POSTA_ADRESİ]',
                '[ŞİRKET_ADRESİ]',
            ],
            [
                now()->locale('tr')->translatedFormat('j F Y'),
                e(self::companyName()),
                e(self::contactEmail()),
                e(self::companyAddress()),
            ],
            $body
        );
    }

    public static function hasBlockingPlaceholders(string $body): bool
    {
        $stripped = str_replace(self::ALLOWED_IN_PUBLIC_BODY, '', $body);

        return (bool) preg_match('/\[[A-ZÇĞİÖŞÜ0-9_\s-]+\]/u', $stripped);
    }
}
