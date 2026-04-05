<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('member.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $emailName = Str::before($validated['email'], '@');

        $user = User::query()->create([
            'name' => Str::of($emailName)->replace(['.', '_', '-'], ' ')->title()->toString(),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('member.dashboard'))->with('success', 'Pendaftaran berjaya. Selamat datang!');
    }

    public function showLogin(): View
    {
        return view('member.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata laluan tidak sah.'])->onlyInput('email');
        }

        if (Auth::user()?->is_admin) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akaun admin sila log masuk di portal admin.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('member.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah log keluar.');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $throwable) {
            return redirect()->route('member.login')->with('error', 'Log masuk Google gagal. Sila cuba lagi.');
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('member.login')->with('error', 'Akaun Google anda tiada email yang sah.');
        }

        $user = User::query()->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $name = trim((string) $googleUser->getName());

            $user = User::query()->create([
                'name' => $name !== '' ? $name : Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(40)),
                'is_admin' => false,
            ]);
        } elseif (! $user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('member.dashboard'))->with('success', 'Log masuk Google berjaya.');
    }
}
