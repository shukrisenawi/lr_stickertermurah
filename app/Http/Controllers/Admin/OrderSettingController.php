<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\StickerPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderSettingController extends Controller
{
    public function __construct(private readonly StickerPricingService $stickerPricing) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Order', [
            'minimumA3SheetsWithoutDesign' => $this->stickerPricing->minimumA3SheetsWithoutDesign(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'minimum_a3_sheets_without_design' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        Setting::setValue(
            StickerPricingService::MIN_A3_SHEETS_SETTING_KEY,
            $validated['minimum_a3_sheets_without_design'],
        );

        return redirect()->route('admin.settings.order.edit')
            ->with('success', 'Minimum kertas order berjaya dikemaskini.');
    }
}
