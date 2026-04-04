<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StickerDesign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StickerDesignController extends Controller
{
    public function index(): View
    {
        return view('admin.designs.index', [
            'designs' => StickerDesign::query()->with('category')->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.designs.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $request->file('image')?->store('sticker-designs', 'public');

        StickerDesign::query()->create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.designs.index')->with('success', 'Design sticker berjaya ditambah.');
    }

    public function edit(StickerDesign $design): View
    {
        return view('admin.designs.edit', [
            'design' => $design,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StickerDesign $design): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $design->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('sticker-designs', 'public');
        }

        $design->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.designs.index')->with('success', 'Design sticker berjaya dikemaskini.');
    }

    public function destroy(StickerDesign $design): RedirectResponse
    {
        if ($design->image_path) {
            Storage::disk('public')->delete($design->image_path);
        }

        $design->delete();

        return redirect()->route('admin.designs.index')->with('success', 'Design sticker berjaya dipadam.');
    }
}
