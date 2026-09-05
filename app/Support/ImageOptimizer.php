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
        ?string $watermark = null,
    ): string {
        $sourcePath = $file->getRealPath();
        $mime = $file->getMimeType();

        if (! is_string($sourcePath)) {
            if ($watermark !== null) {
                throw new \RuntimeException('Imej preview tidak dapat diproses.');
            }

            return $file->store($directory, $disk);
        }

        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagewebp')) {
            if ($watermark !== null) {
                throw new \RuntimeException('Pemprosesan watermark imej tidak tersedia.');
            }

            return $file->store($directory, $disk);
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            'image/gif' => @imagecreatefromgif($sourcePath),
            default => false,
        };

        if (! $source) {
            if ($watermark !== null) {
                throw new \RuntimeException('Format imej preview tidak dapat diproses.');
            }

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

        if ($watermark !== null && $watermark !== '') {
            self::applyWatermark($target, $watermark);
        }

        ob_start();
        $written = imagewebp($target, null, max(1, min(100, $quality)));
        $contents = ob_get_clean();
        imagedestroy($target);

        if (! $written || ! is_string($contents) || $contents === '') {
            if ($watermark !== null) {
                throw new \RuntimeException('Imej preview tidak dapat disimpan.');
            }

            return $file->store($directory, $disk);
        }

        $outputPath = $directory.'/'.Str::uuid().'.webp';
        if (! Storage::disk($disk)->put($outputPath, $contents)) {
            if ($watermark !== null) {
                throw new \RuntimeException('Imej preview tidak dapat disimpan.');
            }

            return $file->store($directory, $disk);
        }

        return $outputPath;
    }

    private static function applyWatermark($image, string $text): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 96);
        $foreground = imagecolorallocatealpha($image, 255, 255, 255, 82);
        $fontPath = self::watermarkFont();

        imagealphablending($image, true);

        if ($fontPath && function_exists('imagettftext') && function_exists('imagettfbbox')) {
            $fontSize = max(18, min(32, (int) round(min($width, $height) / 30)));
            $box = imagettfbbox($fontSize, 25, $fontPath, $text);
            $textWidth = abs($box[2] - $box[0]);
            $stepX = max(280, $textWidth + 160);
            $stepY = max(120, $fontSize * 4);

            for ($row = 0, $y = -$height; $y < $height * 2; $row++, $y += $stepY) {
                $offset = $row % 2 === 0 ? 0 : (int) ($stepX / 2);

                for ($x = -$width + $offset; $x < $width * 2; $x += $stepX) {
                    imagettftext($image, $fontSize, 25, $x, $y, $shadow, $fontPath, $text);
                    imagettftext($image, $fontSize, 25, $x + 2, $y + 2, $foreground, $fontPath, $text);
                }
            }

            return;
        }

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $stepX = max(220, $textWidth + 80);
        $stepY = imagefontheight($font) + 60;

        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                imagestring($image, $font, $x, $y, $text, $shadow);
                imagestring($image, $font, $x + 1, $y + 1, $text, $foreground);
            }
        }
    }

    private static function watermarkFont(): ?string
    {
        $openBaseDir = trim((string) ini_get('open_basedir'));
        $allowedRoots = $openBaseDir === ''
            ? []
            : array_values(array_filter(array_map(
                static fn (string $root): string => rtrim(str_replace('\\', '/', trim($root)), '/'),
                explode(PATH_SEPARATOR, $openBaseDir),
            )));

        foreach ([
            'C:\\Windows\\Fonts\\arialbd.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
        ] as $fontPath) {
            if ($allowedRoots !== []) {
                $normalizedPath = str_replace('\\', '/', $fontPath);
                $isAllowed = false;

                foreach ($allowedRoots as $root) {
                    if ($root === '' || $root === '/' || $normalizedPath === $root || str_starts_with($normalizedPath, $root.'/')) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (! $isAllowed) {
                    continue;
                }
            }

            if (is_file($fontPath)) {
                return $fontPath;
            }
        }

        return null;
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
