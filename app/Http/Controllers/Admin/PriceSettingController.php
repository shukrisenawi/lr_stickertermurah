<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PriceSettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/PriceSettings/Index', [
            'priceSettings' => PriceSetting::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sticker_type' => ['required', 'string', 'max:255'],
            'qty_from' => ['required', 'integer', 'min:1'],
            'qty_to' => ['nullable', 'integer', 'min:1', 'gte:qty_from'],
            'price_per_a3' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PriceSetting::query()->create([
            'sticker_type' => $validated['sticker_type'],
            'qty_from' => $validated['qty_from'],
            'qty_to' => $validated['qty_to'],
            'price_per_a3' => $validated['price_per_a3'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.price-settings.index')->with('success', 'Harga berjaya ditambah.');
    }

    public function update(Request $request, PriceSetting $priceSetting): RedirectResponse
    {
        $validated = $request->validate([
            'sticker_type' => ['required', 'string', 'max:255'],
            'qty_from' => ['required', 'integer', 'min:1'],
            'qty_to' => ['nullable', 'integer', 'min:1', 'gte:qty_from'],
            'price_per_a3' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $priceSetting->update([
            'sticker_type' => $validated['sticker_type'],
            'qty_from' => $validated['qty_from'],
            'qty_to' => $validated['qty_to'],
            'price_per_a3' => $validated['price_per_a3'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.price-settings.index')->with('success', 'Harga berjaya dikemaskini.');
    }

    public function destroy(PriceSetting $priceSetting): RedirectResponse
    {
        $priceSetting->delete();

        return redirect()->route('admin.price-settings.index')->with('success', 'Harga berjaya dipadam.');
    }
}
