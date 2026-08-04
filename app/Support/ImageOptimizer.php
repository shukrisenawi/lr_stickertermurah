<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ImageOptimizer
{
    public static function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1200,
        int $maxHeight = 1200,
        int $quality = 82,
        string $disk = 'public',
    ): string {
        $sourcePath = $file->getRealPath();
        $mime = $file->getMimeType();

        if (! is_string($sourcePath)) {
            return $file->store($directory, $disk);
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => false,
        };

        if (! $source || ! function_exists('imagewebp')) {
            return $file->store($directory, $disk);
        }

        $source = self::applyOrientation($source, $sourcePath, $mime);
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, $maxWidth / max(1, $sourceWidth), $maxHeight / max(1, $sourceHeight));
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefill($target, 0, 0, $transparent);
        } else {
            imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        }

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );
        imagedestroy($source);

        ob_start();
        $written = imagewebp($target, null, max(1, min(100, $quality)));
        $contents = ob_get_clean();
        imagedestroy($target);

        if (! $written || ! is_string($contents) || $contents === '') {
            return $file->store($directory, $disk);
        }

        $outputPath = $directory.'/'.Str::uuid().'.webp';
        if (! Storage::disk($disk)->put($outputPath, $contents)) {
            return $file->store($directory, $disk);
        }

        return $outputPath;
    }

    private static function applyOrientation($image, string $sourcePath, ?string $mime)
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($sourcePath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        switch ($orientation) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $image = self::rotate($image, 180);
                break;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                break;
            case 5:
                $image = self::rotate($image, -90);
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 6:
                $image = self::rotate($image, -90);
                break;
            case 7:
                $image = self::rotate($image, 90);
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 8:
                $image = self::rotate($image, 90);
                break;
        }

        return $image;
    }

    private static function rotate($image, int $angle)
    {
        $rotated = @imagerotate($image, $angle, 0);
        if (! $rotated) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
