<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PostImageUploader
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const MAX_WIDTH = 1920;

    private const WEBP_QUALITY = 82;

    private const JPEG_QUALITY = 85;

    /**
     * @return array{path: string, fallback: string|null, width: int, height: int}
     */
    public function upload(UploadedFile $file): array
    {
        $this->assertAllowedImage($file);

        if (! extension_loaded('gd')) {
            throw new RuntimeException('Görsel işleme için GD eklentisi gerekli.');
        }

        $basename = Str::lower(Str::random(40));
        $directory = 'posts';

        $source = $this->createImageResource($file);

        if (! $source) {
            throw new RuntimeException('Görsel işlenemedi.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        [$targetWidth, $targetHeight] = $this->scaledDimensions($width, $height);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($file->extension(), ['png', 'webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $webpPath = "{$directory}/{$basename}.webp";
        $jpegPath = "{$directory}/{$basename}.jpg";

        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);
        $absoluteWebp = $disk->path($webpPath);
        $absoluteJpeg = $disk->path($jpegPath);

        imagewebp($canvas, $absoluteWebp, self::WEBP_QUALITY);
        imagejpeg($canvas, $absoluteJpeg, self::JPEG_QUALITY);

        imagedestroy($source);
        imagedestroy($canvas);

        return [
            'path' => $webpPath,
            'fallback' => $jpegPath,
            'width' => $targetWidth,
            'height' => $targetHeight,
        ];
    }

    public function delete(?string $path, ?string $fallback = null): void
    {
        $disk = Storage::disk('public');

        foreach (array_filter([$path, $fallback]) as $file) {
            if ($disk->exists($file)) {
                $disk->delete($file);
            }
        }
    }

    private function assertAllowedImage(UploadedFile $file): void
    {
        $extension = strtolower($file->extension() ?? '');

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('Geçersiz görsel formatı.');
        }

        $mime = $file->getMimeType();

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Geçersiz görsel MIME tipi.');
        }
    }

    /**
     * @return resource|false
     */
    private function createImageResource(UploadedFile $file)
    {
        $path = $file->getRealPath();

        return match (strtolower($file->extension() ?? '')) {
            'jpg', 'jpeg' => imagecreatefromjpeg($path),
            'png' => imagecreatefrompng($path),
            'webp' => imagecreatefromwebp($path),
            default => false,
        };
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function scaledDimensions(int $width, int $height): array
    {
        if ($width <= self::MAX_WIDTH) {
            return [$width, $height];
        }

        $ratio = $height / $width;

        return [self::MAX_WIDTH, (int) round(self::MAX_WIDTH * $ratio)];
    }
}
