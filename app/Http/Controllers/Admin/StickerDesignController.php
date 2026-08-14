<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StickerDesign;
use App\Support\StickerDesignImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StickerDesignController extends Controller
{
    public function index(Request $request): Response
    {
        $activeTag = $this->normalizeTagString((string) $request->input('tag', ''));
        $designs = StickerDesign::query()
            ->with('category')
            ->when($activeTag !== '', fn ($query) => $query->whereJsonContains('tags', $activeTag))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        $designs->getCollection()->transform(function ($design) {
            $design->image_url = $design->image_path
                ? Storage::disk('public')->url($design->image_path)
                : null;
            $design->mobile_image_url = $design->mobile_image_path
                ? Storage::disk('public')->url($design->mobile_image_path)
                : $design->image_url;

            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $design->name);
            $safeName = trim($safeName, '_-');
            $design->ori_url = null;
            if (! empty($safeName)) {
                $oriPath = Storage::disk('local')->path('Ori/');
                $files = \glob($oriPath.$safeName.'.*');
                if (! empty($files)) {
                    $design->ori_url = route('admin.ori.image', ['filename' => \basename($files[0])]);
                }
            }

            return $design;
        });

        return Inertia::render('Admin/Designs/Index', [
            'designs' => $designs,
            'availableTags' => $this->existingTags()->all(),
            'activeTag' => $activeTag,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Designs/Create', [
            'categories' => Category::query()->select('id', 'name')->orderBy('name')->get(),
            'existingTags' => $this->existingTags(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50', 'regex:/^[a-z0-9_\-]+$/'],
        ]);

        $category = Category::query()->findOrFail($validated['category_id']);
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

        $designName = $prefix.'_'.\str_pad((string) $startNumber, 3, '0', \STR_PAD_LEFT);

        $data = [
            'name' => $designName,
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($designName).'-'.Str::lower(Str::random(4)),
            'is_active' => $request->boolean('is_active', true),
            'tags' => $this->normalizeTags($request->input('tags')),
        ];

        if ($request->hasFile('image')) {
            $this->storeOriginalImage($request->file('image'), $designName);
            $data = array_merge($data, StickerDesignImageProcessor::storeVariants($request->file('image')));
        }

        StickerDesign::query()->create($data);

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya ditambah.');
    }

    public function searchTags(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $query = $this->normalizeTagString($query);

        $tags = $this->existingTags();

        if ($query !== '') {
            $tags = $tags->filter(function (string $tag) use ($query) {
                return str_contains($tag, $query);
            })->values();
        }

        return response()->json($tags->take(10));
    }

    public function bulkAddTag(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'design_ids' => ['required', 'array', 'min:1', 'max:500'],
            'design_ids.*' => ['required', 'integer', 'distinct', 'exists:sticker_designs,id'],
            'hashtag' => ['required', 'string', 'max:50', 'regex:/^#?[a-zA-Z0-9_-]+$/'],
        ]);

        $tag = $this->normalizeTagString($validated['hashtag']);

        DB::transaction(function () use ($validated, $tag): void {
            StickerDesign::query()
                ->whereIn('id', $validated['design_ids'])
                ->get()
                ->each(function (StickerDesign $design) use ($tag): void {
                    $design->update([
                        'tags' => $this->normalizeTags(array_merge($design->tags ?? [], [$tag])),
                    ]);
                });
        });

        return redirect()
            ->route('admin.designs.index')
            ->with('success', 'Hashtag #'.$tag.' berjaya ditambah kepada '.count($validated['design_ids']).' design.');
    }

    public function bulkCreate(): Response
    {
        return Inertia::render('Admin/Designs/BulkCreate', [
            'categories' => Category::query()->select('id', 'name', 'prefix')->orderBy('name')->get(),
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $category = Category::query()->findOrFail($validated['category_id']);
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

        $count = 0;
        foreach ($validated['images'] as $image) {
            $designName = $prefix.'_'.\str_pad((string) $startNumber, 3, '0', \STR_PAD_LEFT);

            $data = [
                'name' => $designName,
                'category_id' => $category->id,
                'slug' => Str::slug($designName).'-'.Str::lower(Str::random(4)),
                'is_active' => true,
            ];

            $this->storeOriginalImage($image, $designName);
            $data = array_merge($data, StickerDesignImageProcessor::storeVariants($image));

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
            'categories' => Category::query()->select('id', 'name')->orderBy('name')->get(),
            'existingTags' => $this->existingTags(),
        ]);
    }

    public function update(Request $request, StickerDesign $design): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50', 'regex:/^[a-z0-9_\-]+$/'],
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($validated['name']).'-'.$design->id,
            'is_active' => $request->boolean('is_active'),
            'tags' => $this->normalizeTags($request->input('tags')),
        ];

        if ($request->hasFile('image')) {
            $this->deleteImageVariants($design);
            $this->storeOriginalImage($request->file('image'), $validated['name']);
            $data = array_merge($data, StickerDesignImageProcessor::storeVariants($request->file('image')));
        }

        $design->update($data);

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya dikemaskini.');
    }

    /**
     * Collect unique existing tags from all sticker designs.
     *
     * @return Collection<int, string>
     */
    private function existingTags(): Collection
    {
        return StickerDesign::query()
            ->pluck('tags')
            ->filter()
            ->flatten()
            ->map(fn ($tag) => $this->normalizeTagString((string) $tag))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Normalize an array of tag inputs.
     *
     * @param  array<int, mixed>|null  $tags
     * @return array<int, string>
     */
    private function normalizeTags(?array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        return collect($tags)
            ->map(fn ($tag) => $this->normalizeTagString((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalize a single tag string: lowercase, strip leading #, remove invalid chars.
     */
    private function normalizeTagString(string $tag): string
    {
        $tag = ltrim($tag, '#');
        $tag = mb_strtolower($tag);
        $tag = preg_replace('/[^a-z0-9_\-]/', '', $tag);

        return trim($tag);
    }

    public function destroy(StickerDesign $design): RedirectResponse
    {
        $this->deleteImageVariants($design);

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $design->name);
        $safeName = trim($safeName, '_-');
        if (! empty($safeName)) {
            $oriPath = Storage::disk('local')->path('Ori/');
            foreach (\glob($oriPath.$safeName.'.*') as $file) {
                @\unlink($file);
            }
        }

        $design->delete();

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya dipadam.');
    }

    private function storeOriginalImage(UploadedFile $file, string $designName): void
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $designName);
        $safeName = trim($safeName, '_-');
        if (empty($safeName)) {
            $safeName = 'design';
        }

        $ext = $file->getClientOriginalExtension();

        Storage::disk('local')->put('Ori/'.$safeName.'.'.$ext, $file->get());
    }

    private function deleteImageVariants(StickerDesign $design): void
    {
        $paths = array_values(array_unique(array_filter([
            $design->image_path,
            $design->mobile_image_path,
        ])));

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    public function serveOriImage(string $filename)
    {
        $path = Storage::disk('local')->path('Ori/'.$filename);

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }
}
