<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\StickerDesign;
use App\Support\StickerDesignImageProcessor;
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
                $imagePaths = StickerDesignImageProcessor::storeVariants($uploadedFile);

                StickerDesign::query()->create([
                    'name' => $designName,
                    'category_id' => $category->id,
                    'slug' => Str::slug($designName).'-'.Str::lower(Str::random(4)),
                    'is_active' => true,
                    'tags' => $tags,
                    'image_path' => $imagePaths['image_path'],
                    'mobile_image_path' => $imagePaths['mobile_image_path'],
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
}
