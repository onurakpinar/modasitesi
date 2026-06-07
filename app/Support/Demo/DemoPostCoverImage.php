<?php

namespace App\Support\Demo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DemoPostCoverImage
{
    /** @var array<int, string> Unsplash — moda / stil (ücretsiz kullanım) */
    private const PHOTOS = [
        'https://images.unsplash.com/photo-1483985983449-9f511a8470d5?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1469334031218-e382a71b716b?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1558171813-4c088753af8f?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1617137968427-85924c800a41?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1611652022412-f39ca2e2a369?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1479064551652-0f0935aee7f8?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1571945153237-4929e783af4a?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1523381210434-9e5411bc3b63?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1492707892479-7bc8c5c4cb1a?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1558171813-1c6a0c9f8f2e?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?auto=format&fit=crop&w=1600&h=900&q=80',
        'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?auto=format&fit=crop&w=1600&h=900&q=80',
    ];

    /** @var array<int, array{0: int, 1: int, 2: int}> */
    private const PALETTES = [
        [45, 55, 72],
        [88, 64, 74],
        [58, 90, 82],
        [120, 95, 75],
        [70, 78, 110],
    ];

    /**
     * @return array{path: string, fallback: string, width: int, height: int}
     */
    public function fetch(int $index, string $label = ''): array
    {
        $url = self::PHOTOS[$index % count(self::PHOTOS)];

        try {
            return $this->downloadAndStore($url);
        } catch (Throwable) {
            return $this->generateFallback($index, $label);
        }
    }

    /**
     * @return array{path: string, fallback: string, width: int, height: int}
     */
    private function downloadAndStore(string $url): array
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD eklentisi gerekli.');
        }

        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'ModaPusula/1.0'])
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Görsel indirilemedi: '.$response->status());
        }

        $source = @imagecreatefromstring($response->body());

        if ($source === false) {
            throw new RuntimeException('Görsel işlenemedi.');
        }

        $width = imagesx($source);
        $height = imagesy($source);

        return $this->storeImage($source, $width, $height);
    }

    /**
     * @return array{path: string, fallback: string, width: int, height: int}
     */
    private function generateFallback(int $index, string $label): array
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

        $title = $this->shortLabel($label, $index);
        $white = imagecolorallocate($canvas, 250, 248, 245);
        $font = 5;
        $textWidth = imagefontwidth($font) * mb_strlen($title);
        $x = (int) max(48, ($width - $textWidth) / 2);
        imagestring($canvas, $font, $x, (int) ($height - 120), $title, $white);

        return $this->storeImage($canvas, $width, $height);
    }

    /**
     * @param  \GdImage  $image
     * @return array{path: string, fallback: string, width: int, height: int}
     */
    private function storeImage($image, int $width, int $height): array
    {
        $basename = Str::lower(Str::random(40));
        $directory = 'posts';
        $webpPath = "{$directory}/{$basename}.webp";
        $jpegPath = "{$directory}/{$basename}.jpg";

        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);

        imagewebp($image, $disk->path($webpPath), 82);
        imagejpeg($image, $disk->path($jpegPath), 85);
        imagedestroy($image);

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
