<?php

namespace App\Support;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Post;

class PostQualityChecker
{
    public const MIN_TITLE_LENGTH = 35;

    public const MIN_EXCERPT_LENGTH = 140;

    public const MAX_EXCERPT_LENGTH = 260;

    public const MIN_WORD_COUNT = 900;

    public const MIN_META_DESCRIPTION_LENGTH = 120;

    public const MAX_META_DESCRIPTION_LENGTH = 160;

    /**
     * @return array{ready: bool, issues: array<int, string>, word_count: int}
     */
    public function analyze(Post $post): array
    {
        $issues = [];
        $wordCount = self::wordCount($post->body ?? '');

        if (mb_strlen($post->title ?? '') < self::MIN_TITLE_LENGTH) {
            $issues[] = 'Başlık en az '.self::MIN_TITLE_LENGTH.' karakter olmalı.';
        }

        $excerptLength = mb_strlen($post->excerpt ?? '');

        if ($excerptLength < self::MIN_EXCERPT_LENGTH || $excerptLength > self::MAX_EXCERPT_LENGTH) {
            $issues[] = 'Özet '.self::MIN_EXCERPT_LENGTH.'–'.self::MAX_EXCERPT_LENGTH.' karakter aralığında olmalı.';
        }

        if ($wordCount < self::MIN_WORD_COUNT) {
            $issues[] = 'İçerik en az '.self::MIN_WORD_COUNT.' kelime olmalı (şu an: '.$wordCount.').';
        }

        if (! $post->category_id) {
            $issues[] = 'Kategori seçilmeli.';
        }

        if (! $post->author_id || ! Author::query()->whereKey($post->author_id)->where('is_active', true)->exists()) {
            $issues[] = 'Aktif bir yazar seçilmeli.';
        }

        if (! filled($post->cover_image)) {
            $issues[] = 'Kapak görseli yüklenmeli.';
        }

        if (! filled($post->cover_image_alt)) {
            $issues[] = 'Kapak görseli alt metni girilmeli.';
        }

        if (! filled($post->meta_title)) {
            $issues[] = 'Meta başlık doldurulmalı.';
        }

        $metaDescriptionLength = mb_strlen($post->meta_description ?? '');

        if ($metaDescriptionLength < self::MIN_META_DESCRIPTION_LENGTH || $metaDescriptionLength > self::MAX_META_DESCRIPTION_LENGTH) {
            $issues[] = 'Meta açıklama '.self::MIN_META_DESCRIPTION_LENGTH.'–'.self::MAX_META_DESCRIPTION_LENGTH.' karakter aralığında olmalı.';
        }

        if (! $post->originality_confirmed_at) {
            $issues[] = 'Özgünlük onayı bekleniyor.';
        }

        if (! $post->human_reviewed_at) {
            $issues[] = 'İnsan kontrolü bekleniyor.';
        }

        return [
            'ready' => $issues === [],
            'issues' => $issues,
            'word_count' => $wordCount,
        ];
    }

    public function isPublishable(Post $post): bool
    {
        return $this->analyze($post)['ready'];
    }

    public static function wordCount(?string $html): int
    {
        $text = html_entity_decode(strip_tags($html ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        if ($text === '') {
            return 0;
        }

        return count(array_filter(explode(' ', $text)));
    }

    public static function requiresPublishValidation(string $status): bool
    {
        return in_array($status, [PostStatus::Published->value, PostStatus::Scheduled->value], true);
    }
}
