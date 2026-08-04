<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Profile/Edit', [
            'whatsappPhone' => preg_replace('/\D+/', '', PaymentSetting::query()->value('admin_phone') ?? '01169409606'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'file', 'image', 'max:4096'],
            'admin_phone' => ['required', 'string', 'max:30'],
        ]);

        $adminPhone = preg_replace('/\D+/', '', $validated['admin_phone']) ?? '';
        if (! preg_match('/^\d{9,15}$/', $adminPhone)) {
            return back()->withErrors(['admin_phone' => 'Nombor WhatsApp tidak sah.'])->withInput();
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $validated['avatar_path'] = ImageOptimizer::store($request->file('avatar'), 'avatars', 256, 256, 78);
        }

        unset($validated['avatar'], $validated['admin_phone']);

        $user->update($validated);

        $paymentSettings = PaymentSetting::query()->first();
        if ($paymentSettings) {
            $paymentSettings->update(['admin_phone' => $adminPhone]);
        } else {
            PaymentSetting::query()->create(['admin_phone' => $adminPhone]);
        }

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Profil admin berjaya dikemaskini.');
    }

    public function editPassword(): Response
    {
        return Inertia::render('Admin/Profile/Password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Kata laluan semasa tidak tepat.',
            ]);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('admin.password.edit')
            ->with('success', 'Kata laluan admin berjaya dikemaskini.');
    }
}
