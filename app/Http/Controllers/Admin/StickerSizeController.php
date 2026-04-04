<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StickerSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StickerSizeController extends Controller
{
    public function index(): View
    {
        return view('admin.sizes.index', [
            'sizes' => StickerSize::query()->orderByDesc('is_default')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.sizes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'width_cm' => ['nullable', 'numeric', 'min:0.1'],
            'height_cm' => ['nullable', 'numeric', 'min:0.1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            StickerSize::query()->update(['is_default' => false]);
        }

        StickerSize::query()->create([
            ...$validated,
            'is_default' => $request->boolean('is_default'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz/harga berjaya ditambah.');
    }

    public function edit(StickerSize $size): View
    {
        return view('admin.sizes.edit', compact('size'));
    }

    public function update(Request $request, StickerSize $size): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'width_cm' => ['nullable', 'numeric', 'min:0.1'],
            'height_cm' => ['nullable', 'numeric', 'min:0.1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            StickerSize::query()->whereKeyNot($size->id)->update(['is_default' => false]);
        }

        $size->update([
            ...$validated,
            'is_default' => $request->boolean('is_default'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz/harga berjaya dikemaskini.');
    }

    public function destroy(StickerSize $size): RedirectResponse
    {
        $size->delete();

        return redirect()->route('admin.sizes.index')->with('success', 'Saiz/harga berjaya dipadam.');
    }
}
