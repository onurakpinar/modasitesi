<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SecureImageUploader
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public function upload(UploadedFile $file, string $directory, int $maxWidth = 1920): string
    {
        $this->assertAllowedImage($file);

        if (! extension_loaded('gd')) {
            throw new RuntimeException('Görsel işleme için GD eklentisi gerekli.');
        }

        $source = $this->createImageResource($file);

        if (! $source) {
            throw new RuntimeException('Görsel işlenemedi.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        [$targetWidth, $targetHeight] = $this->scaledDimensions($width, $height, $maxWidth);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array(strtolower($file->extension() ?? ''), ['png', 'webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $basename = Str::lower(Str::random(40));
        $path = "{$directory}/{$basename}.jpg";
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);
        imagejpeg($canvas, $disk->path($path), 85);

        imagedestroy($source);
        imagedestroy($canvas);

        return $path;
    }

    private function assertAllowedImage(UploadedFile $file): void
    {
        $extension = strtolower($file->extension() ?? '');

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('Geçersiz görsel formatı.');
        }

        $mime = $file->getMimeType();

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
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
    private function scaledDimensions(int $width, int $height, int $maxWidth): array
    {
        if ($width <= $maxWidth) {
            return [$width, $height];
        }

        $ratio = $height / $width;

        return [$maxWidth, (int) round($maxWidth * $ratio)];
    }
}
