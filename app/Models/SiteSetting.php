<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value', 'group'])]
class SiteSetting extends Model
{
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("site_setting.{$key}", function () use ($key, $default) {
            $setting = static::query()->where('key', $key)->value('value');

            return $setting ?? $default;
        });
    }

    public static function set(string $key, ?string $value, ?string $group = null): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("site_setting.{$key}");
    }
}
