<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StickerDesign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StickerDesignController extends Controller
{
    public function index(): Response
    {
        $designs = StickerDesign::query()->with('category')->latest()->paginate(12);

        $designs->getCollection()->transform(function ($design) {
            $design->image_url = $design->image_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($design->image_path)
                : null;

            return $design;
        });

        return Inertia::render('Admin/Designs/Index', [
            'designs' => $designs,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Designs/Create', [
            'categories' => \App\Models\Category::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $category = \App\Models\Category::query()->findOrFail($validated['category_id']);
        $prefix = $category->prefix ?: Str::upper(Str::substr($category->name, 0, 2));

        $lastDesign = StickerDesign::query()
            ->where('category_id', $category->id)
            ->where('name', 'like', $prefix . '\_%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(name, ?, -1) AS UNSIGNED) DESC', ['_'])
            ->first();

        $startNumber = 1;
        if ($lastDesign) {
            $parts = \explode('_', $lastDesign->name);
            $lastNum = (int) \end($parts);
            $startNumber = $lastNum + 1;
        }

        $designName = $prefix . '_' . \str_pad((string) $startNumber, 3, '0', \STR_PAD_LEFT);

        $data = [
            'name' => $designName,
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($designName) . '-' . Str::lower(Str::random(4)),
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->processAndStoreImage($request->file('image'));
            $this->storeOriginalImage($request->file('image'), $designName);
        }

        StickerDesign::query()->create($data);

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya ditambah.');
    }

    public function bulkCreate(): Response
    {
        return Inertia::render('Admin/Designs/BulkCreate', [
            'categories' => \App\Models\Category::query()->select('id', 'name', 'prefix')->orderBy('name')->get(),
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $category = \App\Models\Category::query()->findOrFail($validated['category_id']);
        $prefix = $category->prefix ?: Str::upper(Str::substr($category->name, 0, 2));

        $lastDesign = StickerDesign::query()
            ->where('category_id', $category->id)
            ->where('name', 'like', $prefix . '\_%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(name, ?, -1) AS UNSIGNED) DESC', ['_'])
            ->first();

        $startNumber = 1;
        if ($lastDesign) {
            $parts = \explode('_', $lastDesign->name);
            $lastNum = (int) \end($parts);
            $startNumber = $lastNum + 1;
        }

        $count = 0;
        foreach ($validated['images'] as $image) {
            $designName = $prefix . '_' . \str_pad((string) $startNumber, 3, '0', \STR_PAD_LEFT);

            $data = [
                'name' => $designName,
                'category_id' => $category->id,
                'slug' => Str::slug($designName) . '-' . Str::lower(Str::random(4)),
                'is_active' => true,
            ];

            $data['image_path'] = $this->processAndStoreImage($image);
            $this->storeOriginalImage($image, $designName);

            StickerDesign::query()->create($data);
            $startNumber++;
            $count++;
        }

        return redirect()->route('admin.designs.index')->with('success', "{$count} design berjaya ditambah secara pukal.");
    }

    public function edit(StickerDesign $design): Response
    {
        return Inertia::render('Admin/Designs/Edit', [
            'design' => $design->load('category'),
            'categories' => \App\Models\Category::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StickerDesign $design): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($validated['name']) . '-' . $design->id,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($design->image_path) {
                Storage::disk('public')->delete($design->image_path);
            }
            $data['image_path'] = $this->processAndStoreImage($request->file('image'));
            $this->storeOriginalImage($request->file('image'), $validated['name']);
        }

        $design->update($data);

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya dikemaskini.');
    }

    public function destroy(StickerDesign $design): RedirectResponse
    {
        if ($design->image_path) {
            Storage::disk('public')->delete($design->image_path);
        }
        $design->delete();

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya dipadam.');
    }

    private function storeOriginalImage(UploadedFile $file, string $designName): void
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $designName);
        $safeName = trim($safeName, '_-');
        if (empty($safeName)) $safeName = 'design';

        $ext = $file->getClientOriginalExtension();

        $file->storeAs('Ori', $safeName . '.' . $ext, 'local');
    }

    public function serveOriImage(string $filename)
    {
        $path = storage_path('app/Ori/' . $filename);

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    private function processAndStoreImage(UploadedFile $file): string
    {
        $targetSize = 350;
        $rotation = mt_rand(-8, 8);

        // Load source image
        $srcPath = $file->getRealPath();
        $srcInfo = \getimagesize($srcPath);
        $srcType = $srcInfo[2] ?? \IMAGETYPE_JPEG;

        switch ($srcType) {
            case \IMAGETYPE_JPEG:
                $srcImage = \imagecreatefromjpeg($srcPath);
                break;
            case \IMAGETYPE_PNG:
                $srcImage = \imagecreatefrompng($srcPath);
                break;
            case \IMAGETYPE_WEBP:
                $srcImage = \imagecreatefromwebp($srcPath);
                break;
            default:
                $srcImage = \imagecreatefromjpeg($srcPath);
        }

        if (! $srcImage) {
            return $file->store('designs', 'public');
        }

        $srcW = \imagesx($srcImage);
        $srcH = \imagesy($srcImage);

        // Create square canvas 500x500
        $canvas = \imagecreatetruecolor($targetSize, $targetSize);
        \imagefill($canvas, 0, 0, \imagecolorallocate($canvas, 255, 255, 255));

        // Resize and crop to square (center crop)
        $minDim = min($srcW, $srcH);
        $srcX = (int) (($srcW - $minDim) / 2);
        $srcY = (int) (($srcH - $minDim) / 2);

        \imagecopyresampled($canvas, $srcImage, 0, 0, $srcX, $srcY, $targetSize, $targetSize, $minDim, $minDim);
        \imagedestroy($srcImage);

        // Apply slight rotation (tilt)
        $rotated = \imagerotate($canvas, $rotation, \imagecolorallocate($canvas, 255, 255, 255));
        \imagedestroy($canvas);

        // Get rotated dimensions and crop back to 500x500
        $rotW = \imagesx($rotated);
        $rotH = \imagesy($rotated);
        $cropX = (int) (($rotW - $targetSize) / 2);
        $cropY = (int) (($rotH - $targetSize) / 2);

        $final = \imagecreatetruecolor($targetSize, $targetSize);
        \imagecopy($final, $rotated, 0, 0, $cropX, $cropY, $targetSize, $targetSize);
        \imagedestroy($rotated);

        // Add watermark (logo)
        $logoPath = public_path('images/logo-baru.png');
        if (\file_exists($logoPath)) {
            $logoInfo = \getimagesize($logoPath);
            $logoType = $logoInfo[2] ?? \IMAGETYPE_PNG;

            switch ($logoType) {
                case \IMAGETYPE_PNG:
                    $logoImage = \imagecreatefrompng($logoPath);
                    break;
                case \IMAGETYPE_JPEG:
                    $logoImage = \imagecreatefromjpeg($logoPath);
                    break;
                default:
                    $logoImage = false;
            }

            if ($logoImage) {
                // Resize logo to 120x120 max
                $logoW = \imagesx($logoImage);
                $logoH = \imagesy($logoImage);
                $maxLogoSize = 200;
                $scale = min($maxLogoSize / $logoW, $maxLogoSize / $logoH);
                $newLogoW = (int) ($logoW * $scale);
                $newLogoH = (int) ($logoH * $scale);

                $resizedLogo = \imagecreatetruecolor($newLogoW, $newLogoH);
                // Preserve transparency for PNG
                \imagealphablending($resizedLogo, false);
                \imagesavealpha($resizedLogo, true);
                $transparent = \imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
                \imagefill($resizedLogo, 0, 0, $transparent);

                \imagecopyresampled($resizedLogo, $logoImage, 0, 0, 0, 0, $newLogoW, $newLogoH, $logoW, $logoH);
                \imagedestroy($logoImage);

                // Place logo at center with 50% opacity
                $posX = \max(0, (int) (($targetSize - $newLogoW) / 2));
                $posY = \max(0, (int) (($targetSize - $newLogoH) / 2));

                // Clamp source dimensions to fit destination
                $srcW = $newLogoW;
                $srcH = $newLogoH;
                if ($posX + $srcW > $targetSize) $srcW = $targetSize - $posX;
                if ($posY + $srcH > $targetSize) $srcH = $targetSize - $posY;

                \imagealphablending($final, true);
                $this->imagecopymergeAlpha($final, $resizedLogo, $posX, $posY, 0, 0, $srcW, $srcH, 50);
                \imagedestroy($resizedLogo);
            }
        }

        // Save processed image
        $filename = 'designs/' . Str::uuid() . '.png';
        $outputPath = storage_path('app/public/' . $filename);

        if (! \is_dir(\dirname($outputPath))) {
            \mkdir(\dirname($outputPath), 0755, true);
        }

        \imagepng($final, $outputPath, 6);
        \imagedestroy($final);

        return $filename;
    }

    /**
     * Merge two images with alpha transparency support.
     */
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
