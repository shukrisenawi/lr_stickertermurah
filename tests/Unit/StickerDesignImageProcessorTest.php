<?php

namespace Tests\Unit;

use App\Support\StickerDesignImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StickerDesignImageProcessorTest extends TestCase
{
    public function test_it_stores_large_and_mobile_watermarked_variants(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('design.jpg', 1600, 1200);

        $paths = StickerDesignImageProcessor::storeVariants($file);

        $this->assertNotSame($paths['image_path'], $paths['mobile_image_path']);
        $this->assertTrue(Storage::disk('public')->exists($paths['image_path']));
        $this->assertTrue(Storage::disk('public')->exists($paths['mobile_image_path']));

        $largeDimensions = getimagesize(Storage::disk('public')->path($paths['image_path']));
        $mobileDimensions = getimagesize(Storage::disk('public')->path($paths['mobile_image_path']));

        $this->assertSame(900, $largeDimensions[0]);
        $this->assertSame(900, $largeDimensions[1]);
        $this->assertSame(240, $mobileDimensions[0]);
        $this->assertSame(240, $mobileDimensions[1]);
    }

    public function test_it_keeps_both_sides_of_a_wide_design(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('wide-design.jpg', 2400, 800);
        $source = imagecreatefromjpeg($file->getRealPath());
        $white = imagecolorallocate($source, 255, 255, 255);
        $leftColor = imagecolorallocate($source, 230, 30, 170);
        $rightColor = imagecolorallocate($source, 30, 180, 230);
        imagefill($source, 0, 0, $white);
        imagefilledrectangle($source, 0, 0, 799, 799, $leftColor);
        imagefilledrectangle($source, 1600, 0, 2399, 799, $rightColor);
        imagejpeg($source, $file->getRealPath(), 100);
        imagedestroy($source);

        $paths = StickerDesignImageProcessor::storeVariants($file);
        $largePath = Storage::disk('public')->path($paths['image_path']);
        $largeInfo = getimagesize($largePath);
        $large = match ($largeInfo[2]) {
            IMAGETYPE_WEBP => imagecreatefromwebp($largePath),
            IMAGETYPE_PNG => imagecreatefrompng($largePath),
            default => imagecreatefromjpeg($largePath),
        };
        $width = imagesx($large);
        $height = imagesy($large);
        $hasLeftContent = false;
        $hasRightContent = false;

        for ($x = 0; $x < $width; $x += 6) {
            for ($y = 0; $y < $height; $y += 6) {
                $color = imagecolorat($large, $x, $y);
                $red = ($color >> 16) & 0xFF;
                $green = ($color >> 8) & 0xFF;
                $blue = $color & 0xFF;

                if ($x < $width * 0.3 && $red > 150 && $blue > 100 && $green < 110) {
                    $hasLeftContent = true;
                }
                if ($x > $width * 0.7 && $green > 120 && $blue > 150 && $red < 110) {
                    $hasRightContent = true;
                }
            }
        }

        imagedestroy($large);

        $this->assertTrue($hasLeftContent);
        $this->assertTrue($hasRightContent);
    }
}
