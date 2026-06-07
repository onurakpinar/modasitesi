<?php

namespace App\Support\Editorial\Articles;

class ArticleBatchLoader
{
    /**
     * @return array<string, array{title: string, excerpt: string, body: string, sources: ?string}>
     */
    public static function byTitle(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        /** @var array<string, array{title: string, excerpt: string, body: string, sources?: ?string}> $bySlug */
        $bySlug = require $path;
        $map = [];

        foreach ($bySlug as $article) {
            $title = $article['title'];
            $map[$title] = [
                'title' => $title,
                'excerpt' => $article['excerpt'],
                'body' => $article['body'],
                'sources' => $article['sources'] ?? null,
            ];
        }

        return $map;
    }
}
