<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StickerSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StickerSizeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());

        $sizes = StickerSize::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = "%{$search}%";

                $query->where(function (Builder $inner) use ($like): void {
                    $inner->where('name', 'like', $like)
                        ->orWhere('shape', 'like', $like)
                        ->orWhere('width_cm', 'like', $like)
                        ->orWhere('height_cm', 'like', $like)
                        ->orWhere('qty_per_a3', 'like', $like);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Admin/Sizes/Index', [
            'sizes' => $sizes,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sizes/Create');
    }

    public function updateVisibility(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'size_ids' => ['required', 'array', 'min:1'],
            'size_ids.*' => ['integer', 'distinct', 'exists:sticker_sizes,id'],
            'show' => ['required', 'boolean'],
        ]);

        $updated = StickerSize::query()
            ->whereKey($validated['size_ids'])
            ->update(['show' => $validated['show']]);

        $status = $validated['show'] ? 'dipaparkan' : 'disembunyikan';

        return back()->with('success', $updated.' saiz berjaya '.$status.'.');
    }

    public function store(Request $request): RedirectResponse
    {
        $sharedRules = [
            'shape' => ['nullable', 'string', 'max:255'],
            'qty_per_a3' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'show' => ['nullable', 'boolean'],
            'return_to_order' => ['nullable', 'boolean'],
        ];

        if ($request->has('sizes')) {
            $validated = $request->validate([
                'sizes' => ['required', 'array', 'min:1', 'max:50'],
                'sizes.*' => ['required', 'array'],
                'sizes.*.name' => ['required', 'string', 'max:255'],
                'sizes.*.width_cm' => ['required', 'numeric', 'min:0.01'],
                'sizes.*.height_cm' => ['required', 'numeric', 'min:0.01'],
                ...$sharedRules,
            ]);

            $sizeRows = $validated['sizes'];
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'width_cm' => ['required', 'numeric', 'min:0.01'],
                'height_cm' => ['required', 'numeric', 'min:0.01'],
                ...$sharedRules,
            ]);

            $sizeRows = [[
                'name' => $validated['name'],
                'width_cm' => $validated['width_cm'],
                'height_cm' => $validated['height_cm'],
            ]];
        }

        DB::transaction(function () use ($sizeRows, $validated, $request): void {
            foreach ($sizeRows as $sizeRow) {
                StickerSize::query()->create([
                    'name' => $sizeRow['name'],
                    'width_cm' => $sizeRow['width_cm'],
                    'height_cm' => $sizeRow['height_cm'],
                    'shape' => $validated['shape'] ?? null,
                    'qty_per_a3' => $validated['qty_per_a3'] ?? null,
                    'price' => $validated['price'] ?? 0,
                    'is_active' => $request->boolean('is_active', true),
                    'is_default' => $request->boolean('is_default', false),
                    'show' => $request->boolean('show', true),
                ]);
            }
        });

        $sizeCount = count($sizeRows);

        if ($request->boolean('return_to_order')) {
            $message = $sizeCount === 1
                ? 'Saiz berjaya ditambah ke database umum.'
                : $sizeCount.' saiz berjaya ditambah ke database umum.';

            return back()->with('success', $message);
        }

        $message = $sizeCount === 1
            ? 'Saiz berjaya ditambah.'
            : $sizeCount.' saiz berjaya ditambah.';

        return redirect()->route('admin.sizes.index')->with('success', $message);
    }

    public function edit(StickerSize $size): Response
    {
        return Inertia::render('Admin/Sizes/Edit', [
            'size' => $size,
        ]);
    }

    public function update(Request $request, StickerSize $size): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'width_cm' => ['required', 'numeric', 'min:0.01'],
            'height_cm' => ['required', 'numeric', 'min:0.01'],
            'shape' => ['nullable', 'string', 'max:255'],
            'qty_per_a3' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'show' => ['nullable', 'boolean'],
        ]);

        $size->update([
            'name' => $validated['name'],
            'width_cm' => $validated['width_cm'],
            'height_cm' => $validated['height_cm'],
            'shape' => $validated['shape'] ?? null,
            'qty_per_a3' => $validated['qty_per_a3'] ?? null,
            'price' => $validated['price'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
            'show' => $request->boolean('show', true),
        ]);

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz berjaya dikemaskini.');
    }

    public function destroy(StickerSize $size): RedirectResponse
    {
        $size->delete();

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz berjaya dipadam.');
    }
}
