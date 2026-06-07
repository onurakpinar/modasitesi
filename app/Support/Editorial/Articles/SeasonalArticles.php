<?php

namespace App\Support\Editorial\Articles;

class SeasonalArticles
{
    /**
     * @return array<string, array{title: string, excerpt: string, body: string, sources: ?string}>
     */
    public static function map(): array
    {
        return ArticleBatchLoader::byTitle(database_path('content/batches/seasonal.php'));
    }
}
