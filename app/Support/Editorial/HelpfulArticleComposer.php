<?php

namespace App\Support\Editorial;

use App\Support\Editorial\Articles\AccessoriesArticles;
use App\Support\Editorial\Articles\MensFashionArticles;
use App\Support\Editorial\Articles\SeasonalArticles;
use App\Support\Editorial\Articles\StyleGuideArticles;
use App\Support\Editorial\Articles\SustainableArticles;
use App\Support\Editorial\Articles\WomensFashionArticles;

class HelpfulArticleComposer
{
    /**
     * @return array{title: string, excerpt: string, body: string, sources: ?string}|null
     */
    public static function forTitle(string $title): ?array
    {
        static $map = null;

        $map ??= array_merge(
            StyleGuideArticles::map(),
            WomensFashionArticles::map(),
            MensFashionArticles::map(),
            AccessoriesArticles::map(),
            SeasonalArticles::map(),
            SustainableArticles::map(),
        );

        return $map[$title] ?? null;
    }
}
