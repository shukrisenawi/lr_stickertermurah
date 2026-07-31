<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => Category::query()->latest()->paginate(12),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Category::query()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(4)),
            'prefix' => $request->input('prefix') ? Str::upper($request->input('prefix')) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berjaya ditambah.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.$category->id,
            'prefix' => $request->input('prefix') ? Str::upper($request->input('prefix')) : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berjaya dikemaskini.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berjaya dipadam.');
    }
}
