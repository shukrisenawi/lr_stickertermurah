<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\StickerDesign;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[Signature('designs:import {folder} {category} {--tags=} {--limit=0}')]
#[Description('Import banyak gambar design dari folder ke dalam database. --tags=tag1,tag2 --limit=0')]
class ImportDesigns extends Command
{
    public function handle(): int
    {
        $folder = $this->argument('folder');
        $categoryName = $this->argument('category');
        $tagsInput = $this->option('tags') ?? '';
        $limit = (int) $this->option('limit');

        if (! is_dir($folder)) {
            $this->error("Folder tidak wujud: {$folder}");

            return self::FAILURE;
        }

        $category = Category::query()
            ->where('id', $categoryName)
            ->orWhere('name', $categoryName)
            ->first();

        if (! $category) {
            $this->error("Kategori tidak dijumpai: {$categoryName}");

            return self::FAILURE;
        }

        $tags = $this->normalizeTags($tagsInput);

        $prefix = $category->prefix ?: Str::upper(Str::substr($category->name, 0, 2));

        $lastDesign = StickerDesign::query()
            ->where('category_id', $category->id)
            ->where('name', 'like', $prefix.'\_%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(name, ?, -1) AS UNSIGNED) DESC', ['_'])
            ->first();

        $startNumber = 1;
        if ($lastDesign) {
            $parts = \explode('_', $lastDesign->name);
            $lastNum = (int) \end($parts);
            $startNumber = $lastNum + 1;
        }

        $finder = new Finder;
        $finder->files()->in($folder)->name('/\.(jpg|jpeg|png|webp)$/i')->sortByName();

        $files = \iterator_to_array($finder, false);
        if ($limit > 0) {
            $files = \array_slice($files, 0, $limit);
        }

        $total = \count($files);
        if ($total === 0) {
            $this->warn('Tiada gambar dijumpai dalam folder.');

            return self::SUCCESS;
        }

        $this->info("Mengimport {$total} gambar ke kategori {$category->name} dengan prefix {$prefix}");
        if (! empty($tags)) {
            $this->info('Tag: #'.\implode(' #', $tags));
        }

        $bar = $this->output->createProgressBar($total);
        $imported = 0;
        $failed = [];

        foreach ($files as $file) {
            /** @var SplFileInfo $file */
            $designName = $prefix.'_'.\str_pad((string) $startNumber, 3, '0', \STR_PAD_LEFT);

            $uploadedFile = new UploadedFile(
                $file->getRealPath(),
                $file->getFilename(),
                $file->getExtension() ? 'image/'.\strtolower($file->getExtension()) : null,
                null,
                true
            );

            try {
                $this->storeOriginalImage($uploadedFile, $designName);
                $imagePath = $this->processAndStoreImage($uploadedFile);

                StickerDesign::query()->create([
                    'name' => $designName,
                    'category_id' => $category->id,
                    'slug' => Str::slug($designName).'-'.Str::lower(Str::random(4)),
                    'is_active' => true,
                    'tags' => $tags,
                    'image_path' => $imagePath,
                ]);

                $imported++;
                $startNumber++;
            } catch (\Throwable $e) {
                $failed[] = $file->getFilename().': '.$e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Berjaya import {$imported} / {$total} design.");

        if (! empty($failed)) {
            $this->error('Gagal:');
            foreach (\array_slice($failed, 0, 10) as $msg) {
                $this->error('  - '.$msg);
            }
            if (\count($failed) > 10) {
                $this->error('  ... dan '.(\count($failed) - 10).' lagi');
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeTags(string $input): array
    {
        if (trim($input) === '') {
            return [];
        }

        return \collect(\explode(',', $input))
            ->map(fn ($tag) => \ltrim(\trim(\mb_strtolower($tag)), '#'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function storeOriginalImage(UploadedFile $file, string $designName): void
    {
        $safeName = \preg_replace('/[^a-zA-Z0-9_\-]/', '_', $designName);
        $safeName = \trim($safeName, '_-');
        if (empty($safeName)) {
            $safeName = 'design';
        }

        Storage::disk('local')->put('Ori/'.$safeName.'.'.$file->getClientOriginalExtension(), $file->get());
    }

    private function processAndStoreImage(UploadedFile $file): string
    {
        $targetSize = 350;
        $rotation = \mt_rand(-8, 8);

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

        $canvas = \imagecreatetruecolor($targetSize, $targetSize);
        \imagefill($canvas, 0, 0, \imagecolorallocate($canvas, 255, 255, 255));

        $minDim = \min($srcW, $srcH);
        $srcX = (int) (($srcW - $minDim) / 2);
        $srcY = (int) (($srcH - $minDim) / 2);

        \imagecopyresampled($canvas, $srcImage, 0, 0, $srcX, $srcY, $targetSize, $targetSize, $minDim, $minDim);
        \imagedestroy($srcImage);

        $rotated = \imagerotate($canvas, $rotation, \imagecolorallocate($canvas, 255, 255, 255));
        \imagedestroy($canvas);

        $rotW = \imagesx($rotated);
        $rotH = \imagesy($rotated);
        $cropX = (int) (($rotW - $targetSize) / 2);
        $cropY = (int) (($rotH - $targetSize) / 2);

        $final = \imagecreatetruecolor($targetSize, $targetSize);
        \imagecopy($final, $rotated, 0, 0, $cropX, $cropY, $targetSize, $targetSize);
        \imagedestroy($rotated);

        $logoPath = \public_path('images/logo-baru.png');
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
                $logoW = \imagesx($logoImage);
                $logoH = \imagesy($logoImage);
                $maxLogoSize = 200;
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

                $posX = \max(0, (int) (($targetSize - $newLogoW) / 2));
                $posY = \max(0, (int) (($targetSize - $newLogoH) / 2));

                $srcW = $newLogoW;
                $srcH = $newLogoH;
                if ($posX + $srcW > $targetSize) {
                    $srcW = $targetSize - $posX;
                }
                if ($posY + $srcH > $targetSize) {
                    $srcH = $targetSize - $posY;
                }

                \imagealphablending($final, true);
                $this->imagecopymergeAlpha($final, $resizedLogo, $posX, $posY, 0, 0, $srcW, $srcH, 50);
                \imagedestroy($resizedLogo);
            }
        }

        $filename = 'designs/'.Str::uuid().'.png';
        $outputPath = \storage_path('app/public/'.$filename);

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
        $pct = \max(0, \min(100, $pct));
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
                $alpha = \max(0, \min(127, $alpha));

                $r = (int) \round($dstR + ($srcR - $dstR) * ($pct / 100) * ((127 - $srcAlpha) / 127));
                $g = (int) \round($dstG + ($srcG - $dstG) * ($pct / 100) * ((127 - $srcAlpha) / 127));
                $b = (int) \round($dstB + ($srcB - $dstB) * ($pct / 100) * ((127 - $srcAlpha) / 127));

                $newColor = \imagecolorallocatealpha($dstIm, $r, $g, $b, $alpha);
                \imagesetpixel($dstIm, $dx, $dy, $newColor);
            }
        }
    }
}
