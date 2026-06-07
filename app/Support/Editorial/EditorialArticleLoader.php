<?php

namespace App\Support\Editorial;

use RuntimeException;

class EditorialArticleLoader
{
    /**
     * @return array{title: string, excerpt: string, body: string, sources: ?string}
     */
    public static function forTitle(string $title): array
    {
        $slug = self::slugForTitle($title);
        $path = database_path("content/articles/{$slug}.php");

        if (is_file($path)) {
            /** @var array{title: string, excerpt: string, body: string, sources?: ?string} $article */
            $article = require $path;
        } else {
            $article = self::fromBatches($slug);
        }

        if ($article === null) {
            $article = HelpfulArticleComposer::forTitle($title);
        }

        if ($article === null) {
            throw new RuntimeException("Makale dosyası bulunamadı: {$slug}");
        }

        if (($article['title'] ?? '') !== $title) {
            throw new RuntimeException("Başlık uyuşmazlığı: {$slug}");
        }

        $body = $article['body'];
        $expansion = self::expansionForSlug($slug);

        if ($expansion !== null) {
            $body .= $expansion;
        }

        return [
            'title' => $article['title'],
            'excerpt' => $article['excerpt'],
            'body' => $body,
            'sources' => $article['sources'] ?? null,
        ];
    }

    public static function slugForTitle(string $title): string
    {
        $map = self::titleSlugMap();

        if (! isset($map[$title])) {
            throw new RuntimeException("Bilinmeyen başlık: {$title}");
        }

        return $map[$title];
    }

    /**
     * @return array<string, string>
     */
    public static function titleSlugMap(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];

        foreach (EditorialBriefCatalog::definitions() as $brief) {
            $title = (string) $brief['title_suggestion'];
            $map[$title] = self::makeSlug($title);
        }

        return $map;
    }

    /**
     * @return array{title: string, excerpt: string, body: string, sources?: ?string}|null
     */
    private static function fromBatches(string $slug): ?array
    {
        foreach (glob(database_path('content/batches/*.php')) ?: [] as $batchFile) {
            /** @var array<string, array{title: string, excerpt: string, body: string, sources?: ?string}> $batch */
            $batch = require $batchFile;

            if (isset($batch[$slug])) {
                return $batch[$slug];
            }
        }

        return null;
    }

    private static function expansionForSlug(string $slug): ?string
    {
        static $map = null;

        if ($map === null) {
            $map = [];

            foreach (glob(database_path('content/expansions/*.php')) ?: [] as $expansionFile) {
                if (str_contains($expansionFile, 'boost.php')
                    || str_contains($expansionFile, 'mega.php')
                    || str_contains($expansionFile, 'ultra')
                    || str_contains($expansionFile, 'final.php')) {
                    continue;
                }

                /** @var array<string, string> $chunk */
                $chunk = require $expansionFile;

                foreach ($chunk as $key => $html) {
                    $map[$key] = ($map[$key] ?? '').$html;
                }
            }
        }

        return $map[$slug] ?? null;
    }

    private static function makeSlug(string $title): string
    {
        $slug = mb_strtolower($title);
        $slug = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç', '–', '—', ':'],
            ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c', '-', '-', ''],
            $slug
        );
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }
}
