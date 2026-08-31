<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        $shouldPrefillLocalCredentials = Config::get('app.env') === 'local'
            && Config::get('database.connections.mysql.username') === 'root';

        return Inertia::render('Auth/AdminLogin', [
            'defaultEmail' => $shouldPrefillLocalCredentials ? 'admin@sticker' : '',
            'defaultPassword' => $shouldPrefillLocalCredentials ? '123' : '',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->forget('impersonate_admin_id');

            $user = Auth::user();
            if (! $user instanceof User || ! $user->is_admin) {
                Auth::logout();

                return back()->withErrors(['email' => 'Akaun ini bukan admin.'])->onlyInput('email');
            }

            $user->markLoggedIn();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau kata laluan tidak sah.'])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function returnFromImpersonation(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('impersonate_admin_id');

        if (! $adminId || ! ($admin = User::query()->find($adminId)) || ! $admin->is_admin) {
            return redirect()->route('home');
        }

        Auth::login($admin, false);
        $request->session()->forget('impersonate_admin_id');
        $request->session()->regenerate();

        return redirect()->route('admin.customers.index')->with('success', 'Berjaya kembali ke akaun admin.');
    }
}
