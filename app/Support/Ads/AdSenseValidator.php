<?php

namespace App\Support\Ads;

final class AdSenseValidator
{
    public static function isValidClientId(?string $value): bool
    {
        return is_string($value) && preg_match('/^ca-pub-\d{16}$/', $value) === 1;
    }

    public static function isValidPublisherId(?string $value): bool
    {
        return is_string($value) && preg_match('/^pub-\d{16}$/', $value) === 1;
    }

    public static function isValidSlotId(?string $value): bool
    {
        return is_string($value) && preg_match('/^\d{10}$/', $value) === 1;
    }
}
