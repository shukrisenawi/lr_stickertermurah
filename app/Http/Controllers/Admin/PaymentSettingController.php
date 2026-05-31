<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSettingController extends Controller
{
    public function index(): Response
    {
        $settings = PaymentSetting::query()->first();
        if ($settings) {
            if ($settings->bank_logo_path) {
                $settings->bank_logo_url = Storage::disk('public')->url($settings->bank_logo_path);
            }
            if ($settings->qr_image_path) {
                $settings->qr_image_url = Storage::disk('public')->url($settings->qr_image_path);
            }
        }

        return Inertia::render('Admin/PaymentSettings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_no' => ['required', 'string', 'max:255'],
            'bank_account_name' => ['required', 'string', 'max:255'],
            'admin_phone' => ['required', 'string', 'max:30'],
            'admin_email' => ['required', 'email', 'max:255'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'bank_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'qr_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $settings = PaymentSetting::query()->first();

        $data = [
            'bank_name' => $validated['bank_name'],
            'bank_account_no' => $validated['bank_account_no'],
            'bank_account_name' => $validated['bank_account_name'],
            'admin_phone' => $validated['admin_phone'],
            'admin_email' => $validated['admin_email'],
            'deposit_amount' => $validated['deposit_amount'],
        ];

        if ($request->hasFile('bank_logo')) {
            if ($settings?->bank_logo_path) {
                Storage::disk('public')->delete($settings->bank_logo_path);
            }
            $data['bank_logo_path'] = $request->file('bank_logo')->store('payment', 'public');
        }

        if ($request->hasFile('qr_image')) {
            if ($settings?->qr_image_path) {
                Storage::disk('public')->delete($settings->qr_image_path);
            }
            $data['qr_image_path'] = $request->file('qr_image')->store('payment', 'public');
        }

        if ($settings) {
            $settings->update($data);
        } else {
            PaymentSetting::query()->create($data);
        }

        return redirect()->route('admin.payment-settings.index')->with('success', 'Maklumat bayaran berjaya dikemaskini.');
    }
}
