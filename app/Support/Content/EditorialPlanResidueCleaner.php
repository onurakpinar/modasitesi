<?php

namespace App\Support\Content;

class EditorialPlanResidueCleaner
{
    /**
     * @return array{original: string, cleaned: string, matches: list<string>}
     */
    public function clean(string $html): array
    {
        $original = $html;
        $matches = [];

        if (preg_match('/<h2[^>]*>[^<]*plan\s*özeti[^<]*<\/h2>/ui', $html)) {
            $html = preg_replace(
                '/<h2[^>]*>[^<]*plan\s*özeti[^<]*<\/h2>.*$/uis',
                '',
                $html,
                -1,
                $count
            ) ?? $html;

            if ($count > 0) {
                $matches[] = 'plan_ozeti_blogu';
            }
        }

        foreach ($this->safetyPatterns() as $label => $pattern) {
            $html = preg_replace_callback($pattern, function (array $match) use (&$matches, $label) {
                $matches[] = $label;

                return '';
            }, $html) ?? $html;
        }

        $html = $this->normalizeHtml($html);

        return [
            'original' => $original,
            'cleaned' => $html,
            'matches' => $matches,
        ];
    }

    public function hasResidue(string $html): bool
    {
        if (preg_match('/<h2[^>]*>[^<]*plan\s*özeti[^<]*<\/h2>/ui', $html)) {
            return true;
        }

        foreach ($this->safetyPatterns() as $pattern) {
            if (preg_match($pattern, $html)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function safetyPatterns(): array
    {
        return [
            'hedef_okur' => '/<p[^>]*>[^<]*Hedef okur:[^<]*<\/p>/iu',
            'kategori_meta' => '/<p[^>]*>[^<]*;\s*kategori:\s*[a-z0-9_-]+[^<]*<\/p>/iu',
            'adim_kalibi' => '/<p[^>]*>[^<]*—\s*\d+\.\s*adım\s*«[^<]*<\/p>/iu',
            'envanter_boilerplate' => '/<p[^>]*>[^<]*maddesini uygularken[^<]*envanter notu tutun[^<]*<\/p>/iu',
        ];
    }

    private function normalizeHtml(string $html): string
    {
        $html = preg_replace('/<h3>\s*<\/h3>/iu', '', $html) ?? $html;
        $html = preg_replace('/<p>\s*<\/p>/iu', '', $html) ?? $html;
        $html = preg_replace('/\h+/u', ' ', $html) ?? $html;

        return trim($html);
    }
}
