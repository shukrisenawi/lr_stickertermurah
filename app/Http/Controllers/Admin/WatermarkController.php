<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WatermarkController extends Controller
{
    private const STORAGE_DIR = 'Watermarks';
    private const MAX_FILES = 3;

    public function index()
    {
        $files = $this->getWatermarkedFiles();

        return Inertia::render('Admin/Watermark/Index', [
            'files' => $files,
            'config' => [
                'resize_height' => Setting::getValue('watermark_resize_height', ''),
                'watermark_size' => Setting::getValue('watermark_watermark_size', ''),
                'apply_watermark' => Setting::getValue('watermark_apply_watermark', '1'),
            ],
        ]);
    }

    public function saveConfig(Request $request): RedirectResponse
    {
        $request->validate([
            'resize_height' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'watermark_size' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'apply_watermark' => ['nullable', 'in:0,1'],
        ]);

        Setting::setValue('watermark_resize_height', $request->input('resize_height', ''));
        Setting::setValue('watermark_watermark_size', $request->input('watermark_size', ''));
        Setting::setValue('watermark_apply_watermark', $request->input('apply_watermark', '1'));

        return back()->with('success', 'Konfigurasi disimpan.');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'resize_height' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'watermark_size' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'apply_watermark' => ['nullable', 'in:0,1'],
        ]);

        $file = $request->file('image');
        $srcImage = $this->loadImage($file->getRealPath());
        if (! $srcImage) {
            return back()->with('error', 'Gagal membaca gambar.');
        }

        $srcW = \imagesx($srcImage);
        $srcH = \imagesy($srcImage);

        $resizeHeight = (int) $request->input('resize_height', 0);
        if ($resizeHeight > 0) {
            $targetH = $resizeHeight;
            $targetW = (int) \round($srcW * $targetH / $srcH);
        } else {
            $targetH = $srcH;
            $targetW = $srcW;
        }

        $resized = \imagecreatetruecolor($targetW, $targetH);
        \imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
        \imagedestroy($srcImage);

        if ($request->input('apply_watermark', '1') === '1') {
            $watermarkSize = (int) $request->input('watermark_size', 200);
            $this->applyWatermark($resized, $targetW, $targetH, $watermarkSize);
        }

        $dir = Storage::disk('local')->path(self::STORAGE_DIR);
        if (! \is_dir($dir)) {
            \mkdir($dir, 0755, true);
        }

        $filename = 'watermark_' . \time() . '_' . \uniqid() . '.png';
        \imagepng($resized, $dir . '/' . $filename, 6);
        \imagedestroy($resized);

        $this->cleanupOldFiles();

        return back()->with('success', 'Gambar berjaya diproses dengan watermark.');
    }

    public function serve(string $filename)
    {
        $path = Storage::disk('local')->path(self::STORAGE_DIR . '/' . $filename);

        if (! \file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $path = Storage::disk('local')->path(self::STORAGE_DIR . '/' . $filename);

        if (\file_exists($path)) {
            \unlink($path);
        }

        return back()->with('success', 'Gambar dipadam.');
    }

    private function getWatermarkedFiles(): array
    {
        $dir = Storage::disk('local')->path(self::STORAGE_DIR);
        if (! \is_dir($dir)) {
            return [];
        }

        $files = \array_values(\array_filter(\scandir($dir), fn ($f) => $f !== '.' && $f !== '..'));

        \usort($files, function ($a, $b) use ($dir) {
            return \filemtime($dir . '/' . $b) - \filemtime($dir . '/' . $a);
        });

        $result = [];
        foreach ($files as $file) {
            $result[] = [
                'name' => $file,
                'url' => route('admin.watermark.serve', ['filename' => $file]),
                'size' => \filesize($dir . '/' . $file),
                'created_at' => \date('Y-m-d H:i:s', \filemtime($dir . '/' . $file)),
            ];
        }

        return $result;
    }

    private function cleanupOldFiles(): void
    {
        $dir = Storage::disk('local')->path(self::STORAGE_DIR);
        if (! \is_dir($dir)) {
            return;
        }

        $files = \array_values(\array_filter(\scandir($dir), fn ($f) => $f !== '.' && $f !== '..'));

        \usort($files, function ($a, $b) use ($dir) {
            return \filemtime($dir . '/' . $b) - \filemtime($dir . '/' . $a);
        });

        while (\count($files) > self::MAX_FILES) {
            $oldest = \array_pop($files);
            \unlink($dir . '/' . $oldest);
        }
    }

    private function loadImage(string $path): \GdImage|false
    {
        $info = @\getimagesize($path);
        if (! $info) {
            return false;
        }

        return match ($info[2]) {
            \IMAGETYPE_JPEG => @\imagecreatefromjpeg($path),
            \IMAGETYPE_PNG => @\imagecreatefrompng($path),
            \IMAGETYPE_WEBP => @\imagecreatefromwebp($path),
            default => false,
        };
    }

    private function applyWatermark(\GdImage $image, int $imageW, int $imageH, int $maxLogoSize = 200): void
    {
        $logoPath = public_path('images/logo-baru.png');
        if (! \file_exists($logoPath)) {
            return;
        }

        $logoImage = @\imagecreatefrompng($logoPath);
        if (! $logoImage) {
            return;
        }

        $logoW = \imagesx($logoImage);
        $logoH = \imagesy($logoImage);
        $scale = \min($maxLogoSize / $logoW, $maxLogoSize / $logoH);
        $newLogoW = (int) ($logoW * $scale);
        $newLogoH = (int) ($logoH * $scale);

        $resizedLogo = \imagecreatetruecolor($newLogoW, $newLogoH);
        \imagealphablending($resizedLogo, false);
        \imagesavealpha($resizedLogo, true);
        $transparent = \imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
        \imagefill($resizedLogo, 0, 0, $transparent);
        \imagecopyresampled($resizedLogo, $logoImage, 0, 0, 0, 0, $newLogoW, $newLogoH, $logoW, $logoH);
        \imagedestroy($logoImage);

        $posX = \max(0, (int) (($imageW - $newLogoW) / 2));
        $posY = \max(0, (int) (($imageH - $newLogoH) / 2));

        \imagealphablending($image, true);
        $this->imagecopymergeAlpha($image, $resizedLogo, $posX, $posY, 0, 0, $newLogoW, $newLogoH, 50);
        \imagedestroy($resizedLogo);
    }

    private function imagecopymergeAlpha($dstIm, $srcIm, int $dstX, int $dstY, int $srcX, int $srcY, int $srcW, int $srcH, int $pct): void
    {
        $pct = max(0, min(100, $pct));
        $dstW = \imagesx($dstIm);
        $dstH = \imagesy($dstIm);

        for ($x = 0; $x < $srcW; $x++) {
            for ($y = 0; $y < $srcH; $y++) {
                $dx = $dstX + $x;
                $dy = $dstY + $y;
                if ($dx < 0 || $dx >= $dstW || $dy < 0 || $dy >= $dstH) {
                    continue;
                }

                $srcColor = \imagecolorat($srcIm, $srcX + $x, $srcY + $y);
                $srcAlpha = ($srcColor >> 24) & 0x7F;
                $srcR = ($srcColor >> 16) & 0xFF;
                $srcG = ($srcColor >> 8) & 0xFF;
                $srcB = $srcColor & 0xFF;

                if ($srcAlpha === 127) {
                    continue;
                }

                $dstColor = \imagecolorat($dstIm, $dx, $dy);
                $dstR = ($dstColor >> 16) & 0xFF;
                $dstG = ($dstColor >> 8) & 0xFF;
                $dstB = $dstColor & 0xFF;

                $alpha = 127 - ((127 - $srcAlpha) * ($pct / 100));
                $alpha = (int) \round($alpha);
                $alpha = max(0, min(127, $alpha));

                $r = (int) \round($dstR + ($srcR - $dstR) * ($pct / 100) * ((127 - $srcAlpha) / 127));
                $g = (int) \round($dstG + ($srcG - $dstG) * ($pct / 100) * ((127 - $srcAlpha) / 127));
                $b = (int) \round($dstB + ($srcB - $dstB) * ($pct / 100) * ((127 - $srcAlpha) / 127));

                $newColor = \imagecolorallocatealpha($dstIm, $r, $g, $b, $alpha);
                \imagesetpixel($dstIm, $dx, $dy, $newColor);
            }
        }
    }
}
