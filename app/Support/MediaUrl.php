<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    /** @var array<string, bool> */
    private static array $existsCache = [];

    public static function public(?string $path, ?string $fallback = null): ?string
    {
        foreach (array_filter([$path, $fallback]) as $candidate) {
            if (filled($candidate) && self::existsOnPublicDisk($candidate)) {
                return Storage::disk('public')->url($candidate);
            }
        }

        return null;
    }

    private static function existsOnPublicDisk(string $path): bool
    {
        if (! array_key_exists($path, self::$existsCache)) {
            self::$existsCache[$path] = Storage::disk('public')->exists($path);
        }

        return self::$existsCache[$path];
    }
}
