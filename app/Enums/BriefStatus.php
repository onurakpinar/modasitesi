<?php

namespace App\Enums;

enum BriefStatus: string
{
    case Idea = 'idea';
    case Preparing = 'preparing';
    case Review = 'review';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Idea => 'Fikir',
            self::Preparing => 'Hazırlanıyor',
            self::Review => 'Kontrolde',
            self::Completed => 'Tamamlandı',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Idea => 'bg-stone-100 text-stone-700',
            self::Preparing => 'bg-amber-100 text-amber-900',
            self::Review => 'bg-sky-100 text-sky-900',
            self::Completed => 'bg-emerald-100 text-emerald-900',
        };
    }
}
