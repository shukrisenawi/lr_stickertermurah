<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StickerDesign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StickerDesignController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Designs/Index', [
            'designs' => StickerDesign::query()->with('category')->latest()->paginate(12),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Designs/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(4)),
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('designs', 'public');
        }

        StickerDesign::query()->create($data);

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya ditambah.');
    }

    public function edit(StickerDesign $design): Response
    {
        return Inertia::render('Admin/Designs/Edit', [
            'design' => $design->load('category'),
        ]);
    }

    public function update(Request $request, StickerDesign $design): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'slug' => Str::slug($validated['name']) . '-' . $design->id,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('designs', 'public');
        }

        $design->update($data);

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya dikemaskini.');
    }

    public function destroy(StickerDesign $design): RedirectResponse
    {
        $design->delete();

        return redirect()->route('admin.designs.index')->with('success', 'Design berjaya dipadam.');
    }
}
