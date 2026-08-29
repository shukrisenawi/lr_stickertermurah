<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StickerSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StickerSizeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Sizes/Index', [
            'sizes' => StickerSize::query()->latest()->paginate(12),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sizes/Create');
    }

    public function store(Request $request): RedirectResponse
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
        ]);

        StickerSize::query()->create([
            'name' => $validated['name'],
            'width_cm' => $validated['width_cm'],
            'height_cm' => $validated['height_cm'],
            'shape' => $validated['shape'] ?? null,
            'qty_per_a3' => $validated['qty_per_a3'] ?? null,
            'price' => $validated['price'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'is_default' => $request->boolean('is_default', false),
        ]);

        if ($request->boolean('return_to_order')) {
            return back()->with('success', 'Saiz berjaya ditambah ke database umum.');
        }

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz berjaya ditambah.');
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
        ]);

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz berjaya dikemaskini.');
    }

    public function destroy(StickerSize $size): RedirectResponse
    {
        $size->delete();

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz berjaya dipadam.');
    }
}
