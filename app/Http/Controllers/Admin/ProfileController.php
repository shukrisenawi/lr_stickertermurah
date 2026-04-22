<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Profil admin berjaya dikemaskini.');
    }

    public function editPassword(): View
    {
        return view('admin.profile.password');
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
