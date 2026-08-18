<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

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
            'mode' => ['required', 'in:matched,new'],
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

        if ($validated['mode'] === 'matched') {
            $matched = $this->findAddressesByPhone($phone);
            $selected = $matched->firstWhere('id', (int) $request->input('address_id'));

            if (! $selected) {
                return back()->withErrors(['address_id' => 'Sila pilih alamat yang betul.'])->onlyInput('no_tel');
            }

            $user = DB::transaction(function () use ($matched, $phone, $selected, $validated) {
                $user = User::query()->create([
                    'name' => $selected->recipient_name,
                    'no_tel' => $phone,
                    'email' => null,
                    'password' => Hash::make($validated['password']),
                    'is_admin' => false,
                ]);

                $matched->each(fn (CustomerAddress $address) => $address->update([
                    'user_id' => $user->id,
                    'is_default' => $address->id === $selected->id,
                ]));

                return $user;
            });

            return $this->loginAfterRegistration($request, $user);
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

    /** @return array{phone:string|null,account_exists:bool,addresses:array<int,array{id:int,recipient_name:string,address:string,no_hp:string|null,is_default:bool}>} */
    private function lookupPhone(string $input): array
    {
        $phone = $this->normalizePhone($input);

        if ($phone === null) {
            return ['phone' => null, 'account_exists' => false, 'addresses' => []];
        }

        return [
            'phone' => $phone,
            'account_exists' => User::query()->where('no_tel', $phone)->exists(),
            'addresses' => $this->findAddressesByPhone($phone)->map(fn (CustomerAddress $address) => [
                'id' => $address->id,
                'recipient_name' => $address->recipient_name ?: 'Penerima',
                'address' => $address->address,
                'no_hp' => $address->no_hp,
                'is_default' => $address->is_default,
            ])->values()->all(),
        ];
    }

    private function findAddressesByPhone(string $phone)
    {
        return CustomerAddress::query()
            ->whereNull('user_id')
            ->get()
            ->filter(fn (CustomerAddress $address) => $this->normalizePhone($address->no_hp) === $phone)
            ->values();
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

    private function loginAfterRegistration(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectAfterAuth($request, 'Pendaftaran berjaya. Selamat datang!');
    }

    private function redirectAfterAuth(Request $request, string $message): RedirectResponse
    {
        if ($request->boolean('from_order')) {
            return redirect()->route('orders.create')->with('success', $message);
        }

        return redirect()->intended(route('member.dashboard'))->with('success', $message);
    }
}
