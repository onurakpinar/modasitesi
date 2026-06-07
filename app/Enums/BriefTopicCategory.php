<?php

namespace App\Enums;

enum BriefTopicCategory: string
{
    case StyleGuide = 'style_guide';
    case WomensFashion = 'womens_fashion';
    case MensFashion = 'mens_fashion';
    case Accessories = 'accessories';
    case SeasonTrends = 'season_trends';
    case SustainableFashion = 'sustainable_fashion';

    public function label(): string
    {
        return match ($this) {
            self::StyleGuide => 'Stil Rehberi',
            self::WomensFashion => 'Kadın Modası',
            self::MensFashion => 'Erkek Modası',
            self::Accessories => 'Aksesuar',
            self::SeasonTrends => 'Sezon Trendleri',
            self::SustainableFashion => 'Sürdürülebilir Moda',
        };
    }
}
