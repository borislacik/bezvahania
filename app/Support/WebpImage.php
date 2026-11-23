<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Converts any uploaded raster image (JPEG, PNG, GIF, WebP, AVIF) to WebP using GD
 * and stores it on the public disk. All images on the website are WebP (docs/decisions.md).
 */
final class WebpImage
{
    public const MAX_WIDTH = 1920;

    public const QUALITY = 82;

    /**
     * @return string Path on the public disk, e.g. "courses/abc123.webp".
     */
    public static function store(string $sourcePath, string $directory): string
    {
        $contents = @file_get_contents($sourcePath);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw new RuntimeException('The uploaded file is not a supported image.');
        }

        $image = self::resize($image);
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::QUALITY);
        $webp = (string) ob_get_clean();
        imagedestroy($image);

        $path = trim($directory, '/').'/'.Str::random(32).'.webp';
        Storage::disk('public')->put($path, $webp);

        return $path;
    }

    private static function resize(\GdImage $image): \GdImage
    {
        $width = imagesx($image);

        if ($width <= self::MAX_WIDTH) {
            return $image;
        }

        $resized = imagescale($image, self::MAX_WIDTH);

        if ($resized === false) {
            return $image;
        }

        imagedestroy($image);

        return $resized;
    }
}
