<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Discounts/Index', [
            'discounts' => Discount::query()
                ->with(['design', 'size'])
                ->latest()
                ->paginate(12),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Discounts/Create', [
            'designs' => StickerDesign::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'sizes' => StickerSize::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'shape']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sticker_design_id' => ['nullable', 'integer', 'exists:sticker_designs,id'],
            'sticker_size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'min_qty' => ['required', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1', 'gte:min_qty'],
            'type' => ['required', 'string', 'in:fixed,percentage'],
            'value' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Discount::query()->create([
            'name' => $validated['name'],
            'sticker_design_id' => $validated['sticker_design_id'],
            'sticker_size_id' => $validated['sticker_size_id'],
            'min_qty' => $validated['min_qty'],
            'max_qty' => $validated['max_qty'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.discounts.index')->with('success', 'Diskaun berjaya ditambah.');
    }

    public function edit(Discount $discount): Response
    {
        return Inertia::render('Admin/Discounts/Edit', [
            'discount' => $discount,
            'designs' => StickerDesign::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'sizes' => StickerSize::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'shape']),
        ]);
    }

    public function update(Request $request, Discount $discount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sticker_design_id' => ['nullable', 'integer', 'exists:sticker_designs,id'],
            'sticker_size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'min_qty' => ['required', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1', 'gte:min_qty'],
            'type' => ['required', 'string', 'in:fixed,percentage'],
            'value' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $discount->update([
            'name' => $validated['name'],
            'sticker_design_id' => $validated['sticker_design_id'],
            'sticker_size_id' => $validated['sticker_size_id'],
            'min_qty' => $validated['min_qty'],
            'max_qty' => $validated['max_qty'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.discounts.index')->with('success', 'Diskaun berjaya dikemaskini.');
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $discount->delete();

        return redirect()->route('admin.discounts.index')->with('success', 'Diskaun berjaya dipadam.');
    }
}
