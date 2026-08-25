<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthController extends Controller
{
    public function showRegister(Request $request): Response
    {
        $lookup = null;

        if ($request->filled('no_tel')) {
            $lookup = $this->lookupPhone($request->string('no_tel')->toString());
        }

        return Inertia::render('Auth/MemberRegister', [
            'lookup' => $lookup,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_tel' => ['required', 'string', 'max:30'],
            'delivery_phone' => ['nullable', 'string', 'max:30'],
            'mode' => ['required', 'in:new'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $phone = $this->normalizePhone($validated['no_tel']);
        if ($phone === null) {
            return back()->withErrors(['no_tel' => 'Nombor telefon tidak sah.'])->onlyInput('no_tel');
        }

        $deliveryPhone = $this->normalizePhone($validated['delivery_phone'] ?? $validated['no_tel']);
        if ($deliveryPhone === null) {
            return back()->withErrors(['delivery_phone' => 'Nombor telefon penghantaran tidak sah.'])->onlyInput(['no_tel', 'delivery_phone']);
        }

        if (User::query()->where('no_tel', $phone)->exists()) {
            return back()->withErrors(['no_tel' => 'Nombor telefon ini sudah berdaftar. Sila login.'])->onlyInput('no_tel');
        }

        $newAddress = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
        ]);

        $user = DB::transaction(function () use ($newAddress, $phone, $deliveryPhone, $validated) {
            $user = User::query()->create([
                'name' => $newAddress['recipient_name'],
                'no_tel' => $phone,
                'email' => null,
                'password' => Hash::make($validated['password']),
                'is_admin' => false,
            ]);

            CustomerAddress::query()->create([
                'user_id' => $user->id,
                'recipient_name' => $newAddress['recipient_name'],
                'address' => $newAddress['address'],
                'no_hp' => $deliveryPhone,
                'is_default' => true,
            ]);

            return $user;
        });

        return $this->loginAfterRegistration($request, $user);
    }

    public function showLogin(): Response
    {
        return Inertia::render('Auth/MemberLogin');
    }

    public function showForgotPassword(): Response
    {
        return Inertia::render('Auth/MemberForgotPassword');
    }

    public function sendResetLink(Request $request): SymfonyResponse
    {
        $validated = $request->validate([
            'no_tel' => ['required', 'string', 'max:30'],
        ]);

        $phone = $this->normalizePhone($validated['no_tel']);
        if ($phone === null) {
            return back()->withErrors(['no_tel' => 'Nombor telefon tidak sah.']);
        }

        $user = User::query()
            ->where('no_tel', $phone)
            ->where('is_admin', false)
            ->first();

        if (! $user) {
            return back()->withErrors(['no_tel' => 'Akaun ahli untuk nombor ini tidak ditemui.']);
        }

        if (blank($user->email)) {
            return Inertia::location($this->passwordHelpWhatsappUrl($phone));
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::ResetLinkSent
            ? back()->with('success', 'Pautan reset kata laluan telah dihantar ke email anda.')
            : back()->withErrors(['no_tel' => 'Pautan reset baru sahaja dihantar. Sila tunggu sebelum mencuba lagi.']);
    }

    public function showResetPassword(Request $request, string $token): Response
    {
        return Inertia::render('Auth/MemberResetPassword', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $isMember = User::query()
            ->where('email', $validated['email'])
            ->where('is_admin', false)
            ->exists();

        if (! $isMember) {
            return back()->withErrors(['email' => 'Pautan reset kata laluan tidak sah.']);
        }

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'must_change_password' => false,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            },
        );

        return $status === Password::PasswordReset
            ? redirect()->route('member.login')->with('success', 'Kata laluan berjaya ditetapkan semula. Sila login.')
            : back()->withErrors(['email' => 'Pautan reset kata laluan tidak sah atau telah tamat tempoh.']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);
        $phone = $this->normalizePhone($login);
        $user = User::query()
            ->where(function ($query) use ($login, $phone) {
                $query->where('email', $login);
                if ($phone !== null) {
                    $query->orWhere('no_tel', $phone);
                }
            })
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['login' => 'Email/no. HP atau kata laluan tidak sah.'])->onlyInput('login');
        }

        if ($user->is_admin) {
            Auth::logout();

            return back()->withErrors(['login' => 'Akaun admin sila log masuk di portal admin.'])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectAfterAuth($request, 'Login berjaya.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /** @return array{phone:string|null,account_exists:bool} */
    private function lookupPhone(string $input): array
    {
        $phone = $this->normalizePhone($input);

        if ($phone === null) {
            return ['phone' => null, 'account_exists' => false];
        }

        return [
            'phone' => $phone,
            'account_exists' => User::query()->where('no_tel', $phone)->exists(),
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '60'.substr($digits, 1);
        }

        return preg_match('/^60\d{8,12}$/', $digits) === 1 ? $digits : null;
    }

    private function passwordHelpWhatsappUrl(string $phone): string
    {
        $configuredPhone = PaymentSetting::query()->value('admin_phone') ?? '01169409606';
        $whatsappPhone = $this->normalizePhone($configuredPhone) ?? '601169409606';
        $message = rawurlencode("Assalamualaikum, saya perlukan bantuan reset kata laluan untuk akaun ahli bernombor {$phone}.");

        return "https://web.whatsapp.com/send?phone={$whatsappPhone}&text={$message}";
    }

    private function loginAfterRegistration(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectAfterAuth($request, 'Pendaftaran berjaya. Selamat datang!');
    }

    private function redirectAfterAuth(Request $request, string $message): RedirectResponse
    {
        if ($request->user()?->must_change_password) {
            return redirect()
                ->route('member.profile.password')
                ->with('info', 'Kata laluan anda telah ditetapkan semula. Sila cipta kata laluan baharu.');
        }

        if ($request->boolean('from_order')) {
            return redirect()->route('orders.create')->with('success', $message);
        }

        return redirect()->intended(route('member.dashboard'))->with('success', $message);
    }
}
