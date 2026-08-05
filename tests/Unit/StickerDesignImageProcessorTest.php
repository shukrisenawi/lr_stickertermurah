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
}
