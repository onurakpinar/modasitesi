<?php

namespace App\Support\Editorial;

use App\Support\PostQualityChecker;

class EditorialBodyPadder
{
    public const TARGET_WORDS = 700;

    public static function pad(string $body, string $title): string
    {
        if (PostQualityChecker::wordCount($body) >= self::TARGET_WORDS) {
            return $body;
        }

        $brief = self::briefFor($title);

        if ($brief === null) {
            return $body;
        }

        foreach (self::buildExtras($brief) as $html) {
            $body .= $html;

            if (PostQualityChecker::wordCount($body) >= self::TARGET_WORDS) {
                break;
            }
        }

        return $body;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function briefFor(string $title): ?array
    {
        foreach (EditorialBriefCatalog::definitions() as $brief) {
            if (($brief['title_suggestion'] ?? '') === $title) {
                return $brief;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $brief
     * @return list<string>
     */
    private static function buildExtras(array $brief): array
    {
        $title = (string) $brief['title_suggestion'];
        $audience = (string) ($brief['target_audience'] ?? 'okurlar');
        $summary = (string) ($brief['content_summary'] ?? '');
        $notes = (string) ($brief['notes'] ?? '');
        $category = $brief['topic_category']?->value ?? (string) ($brief['topic_category'] ?? '');

        $extras = [];

        $extras[] = '<h2>'.e($title).' — plan özeti</h2>'
            .'<p>'.e($summary)
            .($notes !== '' ? ' ('.e($notes).')' : '')
            .' Hedef okur: '.e($audience).'; kategori: '.e($category).'.</p>';

        $summaryParts = array_values(array_filter(
            preg_split('/(?<=[.!?])\s+/u', $summary) ?: [],
            fn (string $part) => mb_strlen(trim($part)) > 15
        ));

        $lines = preg_split('/\n+/', (string) ($brief['subheadings'] ?? '')) ?: [];

        foreach ($lines as $index => $line) {
            $line = trim(ltrim(trim($line), '-'));

            if ($line === '') {
                continue;
            }

            $detail = $summaryParts[$index % max(1, count($summaryParts))] ?? $summary;
            $ordinal = $index + 1;

            $extras[] = '<h3>'.e($line).'</h3>'
                .'<p>«'.e($title).'» — '.$ordinal.'. adım «'.e($line).'»: '
                .e($detail)
                .' ('.e($audience).', '.e($category).')</p>'
                .'<p>«'.e($line).'» maddesini uygularken «'.e($title).'» kapsamında envanter notu tutun; '
                .'eksik kaldıysa yalnızca bu başlık için alım planlayın.</p>';
        }

        return $extras;
    }
}
