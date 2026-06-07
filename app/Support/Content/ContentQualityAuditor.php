<?php

namespace App\Support\Content;

use App\Models\Post;

class ContentQualityAuditor
{
    public const DEFAULT_TEMPLATE_THRESHOLD = 3;

    /**
     * @return array{
     *     posts: list<array<string, mixed>>,
     *     template_sentences: list<array{sentence: string, count: int, post_ids: list<int>}>,
     *     summary: array{total: int, flagged: int}
     * }
     */
    public function audit(int $templateThreshold = self::DEFAULT_TEMPLATE_THRESHOLD): array
    {
        $posts = Post::query()
            ->withTrashed()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'status', 'body', 'excerpt']);

        $sentenceIndex = [];

        $rows = $posts->map(function (Post $post) use (&$sentenceIndex, $templateThreshold) {
            $plainText = $this->plainText($post->body.' '.($post->excerpt ?? ''));
            $sentences = $this->sentences($plainText);
            $words = $this->words($plainText);

            $duplicateCount = $this->duplicateSentenceCount($sentences);
            $lexicalDiversity = $this->lexicalDiversity($words);

            foreach ($sentences as $sentence) {
                $normalized = $this->normalizeSentence($sentence);

                if (mb_strlen($normalized) < 40) {
                    continue;
                }

                $sentenceIndex[$normalized]['sentence'] ??= $sentence;
                $sentenceIndex[$normalized]['post_ids'][$post->id] = true;
            }

            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status->value ?? (string) $post->status,
                'word_count' => count($words),
                'duplicate_sentences' => $duplicateCount,
                'lexical_diversity' => $lexicalDiversity,
                'template_hits' => 0,
                'flagged' => false,
            ];
        });

        $templateSentences = collect($sentenceIndex)
            ->map(fn (array $entry) => [
                'sentence' => $entry['sentence'],
                'count' => count($entry['post_ids']),
                'post_ids' => array_keys($entry['post_ids']),
            ])
            ->filter(fn (array $entry) => $entry['count'] >= $templateThreshold)
            ->sortByDesc('count')
            ->values()
            ->all();

        $templateMap = collect($templateSentences)
            ->mapWithKeys(fn (array $entry) => [$this->normalizeSentence($entry['sentence']) => $entry['count']]);

        $rows = $rows->map(function (array $row) use ($posts, $templateMap, $templateThreshold) {
            $post = $posts->firstWhere('id', $row['id']);
            $sentences = $this->sentences($this->plainText($post->body.' '.($post->excerpt ?? '')));
            $templateHits = collect($sentences)
                ->map(fn (string $sentence) => $templateMap[$this->normalizeSentence($sentence)] ?? 0)
                ->filter(fn (int $count) => $count >= $templateThreshold)
                ->count();

            $row['template_hits'] = $templateHits;
            $row['flagged'] = $row['duplicate_sentences'] > 0
                || $row['lexical_diversity'] < 0.45
                || $templateHits > 0;

            return $row;
        });

        $flagged = $rows->where('flagged', true)->count();

        return [
            'posts' => $rows->values()->all(),
            'template_sentences' => $templateSentences,
            'summary' => [
                'total' => $rows->count(),
                'flagged' => $flagged,
            ],
        ];
    }

    private function plainText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /**
     * @return list<string>
     */
    private function sentences(string $text): array
    {
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/(?<=[.!?…])\s+/u', $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * @return list<string>
     */
    private function words(string $text): array
    {
        if ($text === '') {
            return [];
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values($tokens);
    }

    /**
     * @param  list<string>  $sentences
     */
    private function duplicateSentenceCount(array $sentences): int
    {
        $counts = [];

        foreach ($sentences as $sentence) {
            $normalized = $this->normalizeSentence($sentence);

            if (mb_strlen($normalized) < 20) {
                continue;
            }

            $counts[$normalized] = ($counts[$normalized] ?? 0) + 1;
        }

        return collect($counts)->filter(fn (int $count) => $count > 1)->sum();
    }

    /**
     * @param  list<string>  $words
     */
    private function lexicalDiversity(array $words): float
    {
        if ($words === []) {
            return 0.0;
        }

        return round(count(array_unique($words)) / count($words), 3);
    }

    private function normalizeSentence(string $sentence): string
    {
        $sentence = mb_strtolower(trim($sentence));
        $sentence = preg_replace('/\s+/u', ' ', $sentence) ?? $sentence;

        return rtrim($sentence, '.!?…');
    }
}
