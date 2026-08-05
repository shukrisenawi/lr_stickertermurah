<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class StickerDesignImageProcessor
{
    /**
     * Store a large preview and a smaller mobile thumbnail from one source image.
     *
     * @return array{image_path: string, mobile_image_path: string}
     */
    public static function storeVariants(UploadedFile $file): array
    {
        if (! is_string($file->getRealPath()) || ! function_exists('imagecreatetruecolor')) {
            $path = self::storeFallback($file);

            return [
                'image_path' => $path,
                'mobile_image_path' => $path,
            ];
        }

        $rotation = mt_rand(-8, 8);
        $imagePath = self::processVariant($file, 900, 84, $rotation) ?? self::storeFallback($file);
        $mobileImagePath = self::processVariant($file, 240, 70, $rotation) ?? $imagePath;

        return [
            'image_path' => $imagePath,
            'mobile_image_path' => $mobileImagePath,
        ];
    }

    private static function storeFallback(UploadedFile $file): string
    {
        $path = $file->store('designs', 'public');

        if (! is_string($path)) {
            throw new \RuntimeException('Imej design tidak dapat disimpan.');
        }

        return $path;
    }

    private static function processVariant(UploadedFile $file, int $targetSize, int $quality, int $rotation): ?string
    {
        $sourcePath = $file->getRealPath();
        if (! is_string($sourcePath)) {
            return null;
        }

        $sourceInfo = @getimagesize($sourcePath);
        $sourceType = is_array($sourceInfo) ? ($sourceInfo[2] ?? null) : null;
        $source = self::createSourceImage($sourcePath, $sourceType);

        if (! $source) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $minDimension = min($sourceWidth, $sourceHeight);
        $sourceX = (int) (($sourceWidth - $minDimension) / 2);
        $sourceY = (int) (($sourceHeight - $minDimension) / 2);

        $canvas = imagecreatetruecolor($targetSize, $targetSize);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetSize,
            $targetSize,
            $minDimension,
            $minDimension,
        );
        imagedestroy($source);

        $rotated = @imagerotate($canvas, $rotation, imagecolorallocate($canvas, 255, 255, 255));
        imagedestroy($canvas);

        if (! $rotated) {
            return null;
        }

        $rotatedWidth = imagesx($rotated);
        $rotatedHeight = imagesy($rotated);
        $cropX = (int) (($rotatedWidth - $targetSize) / 2);
        $cropY = (int) (($rotatedHeight - $targetSize) / 2);
        $final = imagecreatetruecolor($targetSize, $targetSize);
        imagecopy($final, $rotated, 0, 0, $cropX, $cropY, $targetSize, $targetSize);
        imagedestroy($rotated);

        self::addWatermark($final, $targetSize);

        $extension = function_exists('imagewebp') ? 'webp' : 'png';
        $filename = 'designs/'.Str::uuid().'.'.$extension;

        ob_start();
        $written = $extension === 'webp'
            ? @imagewebp($final, null, $quality)
            : @imagepng($final, null, 6);
        $contents = ob_get_clean();
        imagedestroy($final);

        if (! $written || ! is_string($contents) || $contents === '') {
            return null;
        }

        return Storage::disk('public')->put($filename, $contents) ? $filename : null;
    }

    private static function createSourceImage(string $sourcePath, ?int $sourceType)
    {
        return match ($sourceType) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($sourcePath) : false,
            IMAGETYPE_PNG => function_exists('imagecreatefrompng') ? @imagecreatefrompng($sourcePath) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };
    }

    private static function addWatermark($image, int $targetSize): void
    {
        $logoPath = public_path('images/logo-baru.png');
        if (! file_exists($logoPath)) {
            return;
        }

        $logoInfo = @getimagesize($logoPath);
        $logoType = is_array($logoInfo) ? ($logoInfo[2] ?? null) : null;
        $logo = match ($logoType) {
            IMAGETYPE_PNG => @imagecreatefrompng($logoPath),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($logoPath),
            default => false,
        };

        if (! $logo) {
            return;
        }

        $logoWidth = imagesx($logo);
        $logoHeight = imagesy($logo);
        $maxLogoSize = max(48, (int) round($targetSize * 0.28));
        $scale = min(1, $maxLogoSize / max(1, $logoWidth), $maxLogoSize / max(1, $logoHeight));
        $newLogoWidth = max(1, (int) round($logoWidth * $scale));
        $newLogoHeight = max(1, (int) round($logoHeight * $scale));

        $resizedLogo = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
        imagealphablending($resizedLogo, false);
        imagesavealpha($resizedLogo, true);
        $transparent = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
        imagefill($resizedLogo, 0, 0, $transparent);
        imagecopyresampled($resizedLogo, $logo, 0, 0, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);
        imagedestroy($logo);

        $positionX = max(0, (int) (($targetSize - $newLogoWidth) / 2));
        $positionY = max(0, (int) (($targetSize - $newLogoHeight) / 2));
        $copyWidth = min($newLogoWidth, $targetSize - $positionX);
        $copyHeight = min($newLogoHeight, $targetSize - $positionY);

        imagealphablending($image, true);
        self::imagecopymergeAlpha($image, $resizedLogo, $positionX, $positionY, $copyWidth, $copyHeight, 50);
        imagedestroy($resizedLogo);
    }

    private static function imagecopymergeAlpha($destination, $source, int $destinationX, int $destinationY, int $sourceWidth, int $sourceHeight, int $percentage): void
    {
        $percentage = max(0, min(100, $percentage));
        $destinationWidth = imagesx($destination);
        $destinationHeight = imagesy($destination);

        for ($x = 0; $x < $sourceWidth; $x++) {
            for ($y = 0; $y < $sourceHeight; $y++) {
                $targetX = $destinationX + $x;
                $targetY = $destinationY + $y;
                if ($targetX < 0 || $targetX >= $destinationWidth || $targetY < 0 || $targetY >= $destinationHeight) {
                    continue;
                }

                $sourceColor = imagecolorat($source, $x, $y);
                $sourceAlpha = ($sourceColor >> 24) & 0x7F;
                if ($sourceAlpha === 127) {
                    continue;
                }

                $sourceRed = ($sourceColor >> 16) & 0xFF;
                $sourceGreen = ($sourceColor >> 8) & 0xFF;
                $sourceBlue = $sourceColor & 0xFF;
                $destinationColor = imagecolorat($destination, $targetX, $targetY);
                $destinationRed = ($destinationColor >> 16) & 0xFF;
                $destinationGreen = ($destinationColor >> 8) & 0xFF;
                $destinationBlue = $destinationColor & 0xFF;
                $opacity = $percentage / 100;
                $sourceOpacity = (127 - $sourceAlpha) / 127;
                $alpha = (int) round(127 - ((127 - $sourceAlpha) * $opacity));
                $red = (int) round($destinationRed + ($sourceRed - $destinationRed) * $opacity * $sourceOpacity);
                $green = (int) round($destinationGreen + ($sourceGreen - $destinationGreen) * $opacity * $sourceOpacity);
                $blue = (int) round($destinationBlue + ($sourceBlue - $destinationBlue) * $opacity * $sourceOpacity);

                imagesetpixel(
                    $destination,
                    $targetX,
                    $targetY,
                    imagecolorallocatealpha($destination, $red, $green, $blue, max(0, min(127, $alpha))),
                );
            }
        }
    }
}
