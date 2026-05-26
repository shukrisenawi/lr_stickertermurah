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
            'width_mm' => ['required', 'numeric', 'min:0'],
            'height_mm' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        StickerSize::query()->create([
            'name' => $validated['name'],
            'width_mm' => $validated['width_mm'],
            'height_mm' => $validated['height_mm'],
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active', true),
            'is_default' => $request->boolean('is_default', false),
        ]);

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
            'width_mm' => ['required', 'numeric', 'min:0'],
            'height_mm' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $size->update([
            'name' => $validated['name'],
            'width_mm' => $validated['width_mm'],
            'height_mm' => $validated['height_mm'],
            'price' => $validated['price'],
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
