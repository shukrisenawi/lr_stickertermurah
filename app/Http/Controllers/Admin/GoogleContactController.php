<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleContactController extends Controller
{
    public function index(Request $request): View
    {
        $token = (string) $request->session()->get('admin.google_contacts.token', '');

        if ($token === '') {
            return view('admin.contacts.google', [
                'contacts' => [],
                'isConnected' => false,
                'error' => null,
            ]);
        }

        try {
            $contacts = $this->fetchAllContacts($token);

            return view('admin.contacts.google', [
                'contacts' => $contacts,
                'isConnected' => true,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $request->session()->forget('admin.google_contacts.token');

            return view('admin.contacts.google', [
                'contacts' => [],
                'isConnected' => false,
                'error' => 'Sesi Google Contacts tamat atau tidak sah. Sila sambung semula akaun Google.',
            ]);
        }
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->redirectUrl(route('admin.contacts.google.callback'))
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

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('admin.contacts.google.callback'))
                ->user();
        } catch (Throwable) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Tidak dapat sambung ke Google Contacts. Sila cuba lagi.');
        }

        $token = (string) ($googleUser->token ?? '');

        if ($token === '') {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Token Google Contacts tidak diterima. Sila cuba semula.');
        }

        $request->session()->put('admin.google_contacts.token', $token);

        return redirect()->route('admin.contacts.google.index')
            ->with('success', 'Google Contacts berjaya disambungkan.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $request->session()->forget('admin.google_contacts.token');

        return redirect()->route('admin.contacts.google.index')
            ->with('success', 'Sambungan Google Contacts telah diputuskan.');
    }

    /**
     * @return array<int, array{name:string, phones:string, emails:string}>
     */
    private function fetchAllContacts(string $token): array
    {
        $contacts = [];
        $pageToken = null;
        $guard = 0;

        do {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get('https://people.googleapis.com/v1/people/me/connections', [
                    'personFields' => 'names,emailAddresses,phoneNumbers',
                    'pageSize' => 1000,
                    'pageToken' => $pageToken,
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Google API request failed.');
            }

            $payload = $response->json();
            $connections = is_array($payload['connections'] ?? null)
                ? $payload['connections']
                : [];

            foreach ($connections as $person) {
                $name = (string) data_get($person, 'names.0.displayName', '');
                $phones = collect(data_get($person, 'phoneNumbers', []))
                    ->pluck('value')
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->map(fn (string $value) => trim($value))
                    ->unique()
                    ->values();

                $emails = collect(data_get($person, 'emailAddresses', []))
                    ->pluck('value')
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->map(fn (string $value) => trim($value))
                    ->unique()
                    ->values();

                if ($name === '' && $phones->isEmpty() && $emails->isEmpty()) {
                    continue;
                }

                $normalizedName = trim(mb_strtolower($name));
                if ($normalizedName === '' || ! str_starts_with($normalizedName, 'sc ')) {
                    continue;
                }

                $contacts[] = [
                    'name' => $name !== '' ? $name : '-',
                    'phones' => $phones->isNotEmpty() ? $phones->implode(', ') : '-',
                    'emails' => $emails->isNotEmpty() ? $emails->implode(', ') : '-',
                ];
            }

            $pageToken = data_get($payload, 'nextPageToken');
            $guard++;
        } while (is_string($pageToken) && $pageToken !== '' && $guard < 30);

        return $contacts;
    }
}
