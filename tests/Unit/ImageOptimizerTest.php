<?php

namespace Tests\Unit;

use App\Support\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    public function test_it_stores_display_images_as_smaller_webp_files(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('testimonial.jpg', 1600, 1200);

        $path = ImageOptimizer::store($file, 'testimonials', 1200, 900, 80);

        $this->assertStringEndsWith('.webp', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
        $dimensions = getimagesize(Storage::disk('public')->path($path));
        $this->assertLessThanOrEqual(1200, $dimensions[0]);
        $this->assertLessThanOrEqual(900, $dimensions[1]);
    }
}
