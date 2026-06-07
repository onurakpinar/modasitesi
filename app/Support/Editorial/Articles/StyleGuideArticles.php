<?php

namespace App\Support\Editorial\Articles;

class StyleGuideArticles
{
    /**
     * @return array<string, array{title: string, excerpt: string, body: string, sources: ?string}>
     */
    public static function map(): array
    {
        return ArticleBatchLoader::byTitle(database_path('content/batches/style-guide.php'));
    }
}
