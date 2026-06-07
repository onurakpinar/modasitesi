<?php

namespace App\Support\Demo;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DemoPostCoverImage
{
    /** @var array<int, array{0: int, 1: int, 2: int}> */
    private const PALETTES = [
        [45, 55, 72],
        [88, 64, 74],
        [58, 90, 82],
        [120, 95, 75],
        [70, 78, 110],
        [92, 72, 58],
        [60, 68, 88],
        [105, 82, 92],
        [52, 74, 68],
        [98, 88, 70],
    ];

    /**
     * @return array{path: string, fallback: string, width: int, height: int}
     */
    public function fetch(int $index, string $label = ''): array
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Kapak görselleri için GD eklentisi gerekli.');
        }

        $width = 1600;
        $height = 900;
        $canvas = imagecreatetruecolor($width, $height);

        $palette = self::PALETTES[$index % count(self::PALETTES)];
        [$r, $g, $b] = $palette;

        for ($y = 0; $y < $height; $y++) {
            $factor = $y / $height;
            $line = imagecolorallocate(
                $canvas,
                (int) ($r + (20 * $factor)),
                (int) ($g + (15 * $factor)),
                (int) ($b + (25 * $factor))
            );
            imageline($canvas, 0, $y, $width, $y, $line);
        }

        $accent = imagecolorallocatealpha($canvas, 255, 255, 255, 110);
        imagefilledellipse($canvas, (int) ($width * 0.72), (int) ($height * 0.35), 520, 520, $accent);
        imagefilledellipse($canvas, (int) ($width * 0.22), (int) ($height * 0.68), 380, 380, $accent);

        $title = $this->shortLabel($label, $index);
        $white = imagecolorallocate($canvas, 250, 248, 245);
        $shadow = imagecolorallocatealpha($canvas, 20, 18, 16, 40);
        $font = 5;
        $textWidth = imagefontwidth($font) * mb_strlen($title);
        $x = (int) max(48, ($width - $textWidth) / 2);
        $y = (int) ($height - 120);
        imagestring($canvas, $font, $x + 2, $y + 2, $title, $shadow);
        imagestring($canvas, $font, $x, $y, $title, $white);

        $basename = Str::lower(Str::random(40));
        $directory = 'posts';
        $webpPath = "{$directory}/{$basename}.webp";
        $jpegPath = "{$directory}/{$basename}.jpg";

        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);

        imagewebp($canvas, $disk->path($webpPath), 82);
        imagejpeg($canvas, $disk->path($jpegPath), 85);
        imagedestroy($canvas);

        return [
            'path' => $webpPath,
            'fallback' => $jpegPath,
            'width' => $width,
            'height' => $height,
        ];
    }

    private function shortLabel(string $label, int $index): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $label) ?: '';
        $ascii = preg_replace('/[^A-Za-z0-9 ]/', '', $ascii) ?? '';
        $ascii = trim(preg_replace('/\s+/', ' ', $ascii) ?? '');

        if ($ascii === '') {
            return 'Moda Yazisi '.($index + 1);
        }

        return mb_substr($ascii, 0, 42);
    }
}
