<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = request()->user();
        $user->load('customerAddresses');

        $addresses = $user->customerAddresses
            ->sortByDesc('is_default')
            ->values()
            ->map(fn ($addr) => [
                'id' => $addr->id,
                'recipient_name' => $addr->recipient_name ?: $user->name,
                'address' => $addr->address,
                'no_hp' => $addr->no_hp,
                'is_default' => $addr->is_default,
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
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'file', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $validated['avatar_path'] = ImageOptimizer::store($request->file('avatar'), 'avatars', 256, 256, 78);
        }

        unset($validated['avatar']);

        $user->update($validated);

        return back()->with('success', 'Profil berjaya dikemaskini.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'no_hp' => ['required', 'string', 'max:30'],
            'is_default' => ['boolean'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($validated, $user) {
            // Jika is_default, buang default yang lain
            if (! empty($validated['is_default'])) {
                CustomerAddress::query()->where('user_id', $user->id)->update(['is_default' => false]);
            }

            CustomerAddress::query()->create([
                'user_id' => $user->id,
                'recipient_name' => $validated['recipient_name'],
                'address' => $validated['address'],
                'no_hp' => $validated['no_hp'],
                'is_default' => $validated['is_default'] ?? false,
            ]);
        });

        return back()->with('success', 'Alamat baru berjaya ditambah.');
    }

    public function updateAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($request->user(), $address);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'no_hp' => ['required', 'string', 'max:30'],
        ]);

        $address->update($validated);

        return back()->with('success', 'Alamat berjaya dikemaskini.');
    }

    public function destroyAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($request->user(), $address);

        if ($address->is_default) {
            return back()->with('error', 'Tidak boleh memadam alamat utama. Tetapkan alamat lain sebagai utama dahulu.');
        }

        $address->delete();

        return back()->with('success', 'Alamat berjaya dipadam.');
    }

    public function setDefaultAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($request->user(), $address);

        DB::transaction(function () use ($address, $request) {
            CustomerAddress::query()->where('user_id', $request->user()->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('success', 'Alamat utama berjaya ditetapkan.');
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

    private function authorizeAddress($user, CustomerAddress $address): void
    {
        abort_if($address->user_id !== $user->id, 403);
    }
}
