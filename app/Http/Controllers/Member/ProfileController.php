<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user()->load('customerAddresses');

        $addresses = $user->customerAddresses->map(fn ($addr) => [
            'id' => $addr->id,
            'address' => $addr->address,
            'no_hp' => $addr->no_hp,
        ]);

        return Inertia::render('Member/Profile/Edit', [
            'addresses' => $addresses,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'file', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($validated['avatar']);

        $user->update($validated);

        return back()->with('success', 'Profil berjaya dikemaskini.');
    }

    public function updateAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:500'],
            'no_hp' => ['required', 'string', 'max:30'],
        ]);

        $user = $request->user();

        CustomerAddress::query()->updateOrCreate(
            ['user_id' => $user->id, 'address' => $validated['address']],
            ['no_hp' => $validated['no_hp']],
        );

        return back()->with('success', 'Alamat penghantaran berjaya dikemaskini.');
    }

    public function editPassword(): Response
    {
        return Inertia::render('Member/Profile/Password');
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

        return back()->with('success', 'Kata laluan berjaya ditukar.');
    }
}