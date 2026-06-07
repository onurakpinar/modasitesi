<?php

namespace App\Support\Content;

class ContentPlaceholderCleaner
{
    /**
     * @return array{original: string, cleaned: string, matches: list<string>}
     */
    public function clean(string $text): array
    {
        $original = $text;
        $matches = [];

        foreach ($this->patterns() as $label => $pattern) {
            $text = preg_replace_callback($pattern, function (array $match) use (&$matches, $label) {
                $matches[] = $label.': '.trim($match[0]);

                return '';
            }, $text) ?? $text;
        }

        $text = $this->normalizeWhitespace($text);

        return [
            'original' => $original,
            'cleaned' => $text,
            'matches' => $matches,
        ];
    }

    public function hasPlaceholders(string $text): bool
    {
        foreach ($this->patterns() as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function patterns(): array
    {
        return [
            'editoryal_not' => '/\s*Editoryal not:\s*[^.!?]*[.!?]?/iu',
            'talimat_eklenmeli' => '/\s*[^.!?]*\b(?:notu\s+)?eklenmeli\b[^.!?]*[.!?]?/iu',
            'bracket_placeholder' => '/\[[A-ZÇĞİÖŞÜ0-9_\s]{2,}\]/u',
        ];
    }

    private function normalizeWhitespace(string $text): string
    {
        $text = preg_replace('/\h+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+([.!?,;:])/u', '$1', $text) ?? $text;
        $text = preg_replace('/(<\/p>)\s*(<p>)/iu', '$1$2', $text) ?? $text;
        $text = preg_replace('/<p>\s*<\/p>/iu', '', $text) ?? $text;

        return trim($text);
    }
}
