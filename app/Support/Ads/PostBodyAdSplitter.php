<?php

namespace App\Support\Ads;

class PostBodyAdSplitter
{
    private const int PARAGRAPHS_BEFORE_MIDDLE_AD = 4;

    /**
     * @return array{before: string, after: string}
     */
    public static function split(string $html): array
    {
        if ($html === '') {
            return ['before' => '', 'after' => ''];
        }

        $offset = 0;
        $paragraphsFound = 0;
        $length = strlen($html);

        while ($paragraphsFound < self::PARAGRAPHS_BEFORE_MIDDLE_AD) {
            $position = stripos($html, '</p>', $offset);

            if ($position === false) {
                return ['before' => $html, 'after' => ''];
            }

            $offset = $position + 4;
            $paragraphsFound++;
        }

        if ($offset >= $length) {
            return ['before' => $html, 'after' => ''];
        }

        return [
            'before' => substr($html, 0, $offset),
            'after' => substr($html, $offset),
        ];
    }
}
