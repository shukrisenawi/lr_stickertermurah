<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\PriceSetting;
use App\Models\StickerSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DiscountController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Discounts/Index', [
            'discounts' => Discount::query()
                ->with('size')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Discounts/Create', [
            'stickerTypes' => PriceSetting::query()->where('is_active', true)->select('sticker_type')->distinct()->orderBy('sticker_type')->pluck('sticker_type'),
            'sizes' => $this->activeSizes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sticker_type' => ['nullable', 'string', 'max:255'],
            'sticker_size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'min_qty' => ['required', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1', 'gte:min_qty'],
            'type' => ['required', 'string', 'in:fixed,price,percentage'],
            'value' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'expired_at' => ['nullable', 'date'],
        ]);

        Discount::query()->create([
            'name' => $validated['name'],
            'sticker_type' => $validated['sticker_type'],
            'sticker_size_id' => $validated['sticker_size_id'],
            'min_qty' => $validated['min_qty'],
            'max_qty' => $validated['max_qty'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'is_active' => $request->boolean('is_active', true),
            'expired_at' => $validated['expired_at'],
        ]);

        return redirect()->route('admin.discounts.index')->with('success', 'Diskaun berjaya ditambah.');
    }

    public function edit(Discount $discount): Response
    {
        return Inertia::render('Admin/Discounts/Edit', [
            'discount' => $discount,
            'stickerTypes' => PriceSetting::query()->where('is_active', true)->select('sticker_type')->distinct()->orderBy('sticker_type')->pluck('sticker_type'),
            'sizes' => $this->activeSizes(),
        ]);
    }

    public function update(Request $request, Discount $discount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sticker_type' => ['nullable', 'string', 'max:255'],
            'sticker_size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'min_qty' => ['required', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1', 'gte:min_qty'],
            'type' => ['required', 'string', 'in:fixed,price,percentage'],
            'value' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'expired_at' => ['nullable', 'date'],
        ]);

        $discount->update([
            'name' => $validated['name'],
            'sticker_type' => $validated['sticker_type'],
            'sticker_size_id' => $validated['sticker_size_id'],
            'min_qty' => $validated['min_qty'],
            'max_qty' => $validated['max_qty'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'is_active' => $request->boolean('is_active'),
            'expired_at' => $validated['expired_at'],
        ]);

        return redirect()->route('admin.discounts.index')->with('success', 'Diskaun berjaya dikemaskini.');
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $discount->delete();

        return redirect()->route('admin.discounts.index')->with('success', 'Diskaun berjaya dipadam.');
    }

    private function activeSizes(): Collection
    {
        $leadingSizeNumber = static function (string $name): float {
            preg_match('/^\s*(\d+(?:[.,]\d+)?)/', $name, $matches);

            return isset($matches[1]) ? (float) str_replace(',', '.', $matches[1]) : INF;
        };

        return StickerSize::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'shape'])
            ->sort(function (StickerSize $first, StickerSize $second) use ($leadingSizeNumber): int {
                return $leadingSizeNumber($first->name) <=> $leadingSizeNumber($second->name)
                    ?: strcmp($first->name, $second->name);
            })
            ->values();
    }
}
