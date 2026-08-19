<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\GoogleContactConnection;
use App\Models\User;
use App\Services\GoogleContactsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use RuntimeException;
use Throwable;

class GoogleContactController extends Controller
{
    public function index(Request $request, GoogleContactsService $googleContacts): Response
    {
        $connection = $request->user()->googleContactConnection;
        $customers = $connection
            ? User::query()
                ->where('is_admin', false)
                ->where(function ($query): void {
                    $query->whereNotNull('no_tel')
                        ->orWhereHas('customerAddresses', fn ($addressQuery) => $addressQuery->whereNotNull('no_hp'));
                })
                ->with(['customerAddresses' => function ($query): void {
                    $query->orderByDesc('is_default')->orderByDesc('updated_at');
                }])
                ->orderBy('name')
                ->limit(500)
                ->get()
                ->map(fn (User $customer): array => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'no_tel' => $customer->no_tel,
                    'addresses' => $customer->customerAddresses->map(fn (CustomerAddress $address): array => [
                        'id' => $address->id,
                        'recipient_name' => $address->recipient_name,
                        'address' => $address->address,
                        'no_hp' => $address->no_hp,
                        'is_default' => $address->is_default,
                    ])->values(),
                ])->values()
            : collect();

        return Inertia::render('Admin/Contacts/Google', [
            'isConfigured' => $googleContacts->isConfigured(),
            'callbackUrl' => $this->googleRedirectUrl(),
            'connection' => $connection ? [
                'email' => $connection->google_email,
                'connected_at' => $connection->connected_at?->toIso8601String(),
            ] : null,
            'customers' => $customers,
        ]);
    }

    public function redirectToGoogle(GoogleContactsService $googleContacts): RedirectResponse
    {
        if (! $googleContacts->isConfigured()) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', $this->configurationError());
        }

        return $this->googleProvider()
            ->redirectUrl($this->googleRedirectUrl())
            ->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/contacts',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request, GoogleContactsService $googleContacts): RedirectResponse
    {
        if (! $googleContacts->isConfigured()) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', $this->configurationError());
        }

        try {
            $googleUser = $this->googleProvider()
                ->redirectUrl($this->googleRedirectUrl())
                ->user();
        } catch (Throwable) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Tidak dapat menyambung ke Google Contacts. Sila cuba lagi.');
        }

        if (blank($googleUser->token)) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Token Google Contacts tidak diterima. Sila cuba semula.');
        }

        $connection = GoogleContactConnection::query()->firstOrNew([
            'user_id' => $request->user()->id,
        ]);

        $connection->fill([
            'google_id' => $googleUser->getId(),
            'google_email' => $googleUser->getEmail(),
            'access_token' => $googleUser->token,
            'expires_at' => filled($googleUser->expiresIn)
                ? now()->addSeconds((int) $googleUser->expiresIn)
                : null,
            'connected_at' => now(),
        ]);

        if (filled($googleUser->refreshToken)) {
            $connection->refresh_token = $googleUser->refreshToken;
        }

        $connection->save();

        return redirect()->route('admin.contacts.google.index')
            ->with('success', 'Google Contacts berjaya disambungkan.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $request->user()->googleContactConnection()?->delete();

        return redirect()->route('admin.contacts.google.index')
            ->with('success', 'Sambungan Google Contacts telah diputuskan.');
    }

    public function storeManual(Request $request, GoogleContactsService $googleContacts): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($googleContacts->normalizePhone($validated['phone']) === null) {
            return back()->withErrors(['phone' => 'Nombor telefon tidak sah.'])->withInput();
        }

        return $this->createContact(
            $request,
            $googleContacts,
            $validated['name'],
            $validated['phone'],
            $validated['email'] ?? null,
            $validated['address'] ?? null,
        );
    }

    public function storeCustomer(Request $request, GoogleContactsService $googleContacts): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
            'address_id' => ['nullable', 'integer'],
        ]);

        $customer = User::query()
            ->where('is_admin', false)
            ->findOrFail($validated['customer_id']);

        $address = isset($validated['address_id'])
            ? $customer->customerAddresses()->find($validated['address_id'])
            : $customer->customerAddresses()
                ->orderByDesc('is_default')
                ->orderByDesc('updated_at')
                ->first();

        if (isset($validated['address_id']) && ! $address) {
            return back()->withErrors(['address_id' => 'Alamat tidak sepadan dengan customer yang dipilih.']);
        }

        $phone = $address?->no_hp ?: $customer->no_tel;
        if (! is_string($phone) || $googleContacts->normalizePhone($phone) === null) {
            return back()->withErrors(['customer_id' => 'Customer ini tidak mempunyai nombor telefon yang sah.']);
        }

        return $this->createContact(
            $request,
            $googleContacts,
            $address?->recipient_name ?: $customer->name,
            $phone,
            $customer->email,
            $address?->address,
        );
    }

    private function createContact(
        Request $request,
        GoogleContactsService $googleContacts,
        string $name,
        string $phone,
        ?string $email,
        ?string $address,
    ): RedirectResponse {
        $connection = $request->user()->googleContactConnection;

        if (! $connection) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Sambungkan akaun Google sebelum menambah contact.');
        }

        try {
            $result = $googleContacts->createContact($connection, $name, $phone, $email, $address);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Contact tidak dapat disimpan ke Google. Sila sambung semula akaun atau cuba lagi.');
        }

        if (! $result['created']) {
            return back()->with('error', 'Nombor telefon ini sudah wujud dalam Google Contacts sebagai "'.$result['existing_name'].'". Contact baharu tidak disimpan.');
        }

        return back()->with('success', 'Contact "'.$name.'" berjaya disimpan ke Google Contacts.');
    }

    private function configurationError(): string
    {
        return 'Google OAuth belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET dalam fail .env dahulu.';
    }

    private function googleRedirectUrl(): string
    {
        $configuredUrl = trim((string) config('services.google.redirect'));

        return $configuredUrl !== ''
            ? $configuredUrl
            : route('admin.contacts.google.callback');
    }

    private function googleProvider(): AbstractProvider
    {
        $provider = Socialite::driver('google');

        if (! $provider instanceof AbstractProvider) {
            throw new RuntimeException('Provider Google OAuth tidak sah.');
        }

        return $provider;
    }
}
