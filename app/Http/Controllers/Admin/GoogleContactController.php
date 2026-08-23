<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\GoogleContact;
use App\Models\GoogleContactConnection;
use App\Models\User;
use App\Services\GoogleContactsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use RuntimeException;
use Throwable;

class GoogleContactController extends Controller
{
    private const CONTACTS_PER_PAGE = 20;

    public function index(Request $request, GoogleContactsService $googleContacts): Response
    {
        $contactSearch = trim($request->string('q')->toString());
        $sortColumns = [
            'name' => 'name',
            'phone' => 'phone',
            'email' => 'email',
            'address' => 'address',
        ];
        $contactSort = $request->string('sort')->toString();
        if (! array_key_exists($contactSort, $sortColumns)) {
            $contactSort = 'name';
        }

        $contactDirection = $request->string('direction')->toString();
        if (! in_array($contactDirection, ['asc', 'desc'], true)) {
            $contactDirection = 'asc';
        }
        $contactGroup = $request->string('group')->toString();
        if (! in_array($contactGroup, ['company', 'personal'], true)) {
            $contactGroup = 'company';
        }
        $connection = $request->user()->googleContactConnection;

        $contactsError = null;

        if ($connection && $this->contactsNeedSync($connection)) {
            try {
                $googleContacts->syncContacts($connection);
            } catch (Throwable $exception) {
                report($exception);
                $contactsError = 'Data Google Contacts belum dapat disegerakkan. Data cache terakhir dipaparkan jika tersedia.';
            }
        }

        $paginatedContacts = $connection
            ? $connection->contacts()
                ->when($contactSearch !== '', function (Builder $query) use ($contactSearch): void {
                    $like = '%'.$contactSearch.'%';
                    $query->where(function (Builder $searchQuery) use ($like): void {
                        $searchQuery->where('name', 'like', $like)
                            ->orWhere('phone', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('address', 'like', $like);
                    });
                })
                ->when($contactGroup === 'company', fn (Builder $query) => $query->whereRaw('LOWER(name) LIKE ?', ['sc %']))
                ->when($contactGroup === 'personal', fn (Builder $query) => $query->whereRaw('LOWER(name) NOT LIKE ?', ['sc %']))
                ->orderBy($sortColumns[$contactSort], $contactDirection)
                ->orderBy('id')
                ->paginate(self::CONTACTS_PER_PAGE)
                ->withQueryString()
                ->through(fn (GoogleContact $contact): array => $this->contactData($contact))
            : new LengthAwarePaginator([], 0, self::CONTACTS_PER_PAGE, 1, [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]);

        return Inertia::render('Admin/Contacts/Google', [
            'isConfigured' => $googleContacts->isConfigured(),
            'callbackUrl' => $this->googleRedirectUrl(),
            'connection' => $connection ? [
                'email' => $connection->google_email,
                'connected_at' => $connection->connected_at?->toIso8601String(),
            ] : null,
            'contacts' => $paginatedContacts,
            'contactSearch' => $contactSearch,
            'contactSort' => $contactSort,
            'contactDirection' => $contactDirection,
            'contactGroup' => $contactGroup,
            'contactsError' => $contactsError,
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $connection = $request->user()->googleContactConnection;

        if (! $connection) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Sambungkan akaun Google sebelum menambah contact.');
        }

        return Inertia::render('Admin/Contacts/Create', [
            'connection' => [
                'email' => $connection->google_email,
            ],
            'customers' => $this->customerOptions(),
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

        $connection->contacts_synced_at = null;
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

    public function update(Request $request, GoogleContactsService $googleContacts): RedirectResponse
    {
        $validated = $request->validate([
            'resource_name' => ['required', 'string', 'max:255', 'regex:/^people\/[^\/]+$/'],
            'etag' => ['nullable', 'string', 'max:1000'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($googleContacts->normalizePhone($validated['phone']) === null) {
            return back()->withErrors(['phone' => 'Nombor telefon tidak sah.'])->withInput();
        }

        $connection = $request->user()->googleContactConnection;
        if (! $connection) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Sambungkan akaun Google sebelum mengemaskini contact.');
        }

        $localContact = $connection->contacts()
            ->where('resource_name', $validated['resource_name'])
            ->first();

        if (! $localContact) {
            return back()->with('error', 'Contact tidak ditemui dalam cache lokal. Sila segar semula data Google Contacts.');
        }

        $normalizedPhone = $googleContacts->normalizePhone($validated['phone']);
        $existingContact = $this->findLocalContactByPhone($connection, $normalizedPhone, $localContact->id);
        if ($existingContact) {
            return back()->with('error', 'Nombor telefon ini sudah wujud dalam Google Contacts sebagai "'.$existingContact->name.'". Contact tidak dikemaskini.');
        }

        $etag = filled($validated['etag'] ?? null)
            ? $validated['etag']
            : $localContact->etag;

        try {
            $result = $googleContacts->updateContact(
                $connection,
                $validated['resource_name'],
                $etag,
                $validated['name'],
                $validated['phone'],
                $validated['email'] ?? null,
                $validated['address'] ?? null,
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Contact tidak dapat dikemaskini di Google. Sila sambung semula akaun atau cuba lagi.');
        }

        $localContact->update([
            'etag' => $result['etag'],
            'name' => $validated['name'],
            'normalized_phone' => $normalizedPhone,
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return back()->with('success', 'Contact "'.$validated['name'].'" berjaya dikemaskini.');
    }

    public function destroy(Request $request, GoogleContactsService $googleContacts): RedirectResponse
    {
        $validated = $request->validate([
            'resource_name' => ['required', 'string', 'max:255', 'regex:/^people\/[^\/]+$/'],
        ]);

        $connection = $request->user()->googleContactConnection;
        if (! $connection) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Sambungkan akaun Google sebelum memadam contact.');
        }

        $localContact = $connection->contacts()
            ->where('resource_name', $validated['resource_name'])
            ->first();

        if (! $localContact) {
            return back()->with('error', 'Contact tidak ditemui dalam cache lokal. Sila segar semula data Google Contacts.');
        }

        try {
            $googleContacts->deleteContact($connection, $validated['resource_name']);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Contact tidak dapat dipadam daripada Google. Sila sambung semula akaun atau cuba lagi.');
        }

        $localContact->delete();

        return back()->with('success', 'Contact berjaya dipadam daripada Google Contacts.');
    }

    public function bulkDestroy(Request $request, GoogleContactsService $googleContacts): RedirectResponse
    {
        $validated = $request->validate([
            'resource_names' => ['required', 'array', 'min:1', 'max:100'],
            'resource_names.*' => ['required', 'distinct', 'string', 'max:255', 'regex:/^people\/[^\/]+$/'],
        ]);

        $connection = $request->user()->googleContactConnection;
        if (! $connection) {
            return redirect()->route('admin.contacts.google.index')
                ->with('error', 'Sambungkan akaun Google sebelum memadam contact.');
        }

        $localContacts = $connection->contacts()
            ->whereIn('resource_name', $validated['resource_names'])
            ->get();

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($localContacts as $localContact) {
            try {
                $googleContacts->deleteContact($connection, $localContact->resource_name);
                $localContact->delete();
                $deletedCount++;
            } catch (Throwable $exception) {
                report($exception);
                $failedCount++;
            }
        }

        if ($deletedCount === 0) {
            return back()->with('error', 'Contact yang dipilih tidak dapat dipadam daripada Google.');
        }

        $response = back()->with('success', $deletedCount.' contact berjaya dipadam daripada Google Contacts.');
        if ($failedCount > 0) {
            $response->with('error', $failedCount.' contact gagal dipadam. Sila cuba semula.');
        }

        return $response;
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

        $normalizedPhone = $googleContacts->normalizePhone($phone);
        if ($normalizedPhone === null) {
            return back()->withErrors(['phone' => 'Nombor telefon tidak sah.'])->withInput();
        }

        $existingContact = $this->findLocalContactByPhone($connection, $normalizedPhone);
        if ($existingContact) {
            return back()->with('error', 'Nombor telefon ini sudah wujud dalam Google Contacts sebagai "'.$existingContact->name.'". Contact baharu tidak disimpan.');
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

        GoogleContact::query()->updateOrCreate(
            [
                'google_contact_connection_id' => $connection->id,
                'resource_name' => $result['contact']['resource_name'],
            ],
            [
                'etag' => $result['contact']['etag'],
                'name' => $result['contact']['name'],
                'normalized_phone' => $normalizedPhone,
                'phone' => $result['contact']['phone'],
                'email' => $result['contact']['email'],
                'address' => $result['contact']['address'],
            ],
        );

        return redirect()->route('admin.contacts.google.index')
            ->with('success', 'Contact "'.$name.'" berjaya disimpan ke Google Contacts.');
    }

    private function customerOptions()
    {
        return User::query()
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
            ])->values();
    }

    private function contactsNeedSync(GoogleContactConnection $connection): bool
    {
        return $connection->contacts_synced_at === null
            || $connection->contacts_synced_at->lte(now()->subDay());
    }

    private function findLocalContactByPhone(GoogleContactConnection $connection, string $normalizedPhone, ?int $exceptId = null): ?GoogleContact
    {
        return $connection->contacts()
            ->where('normalized_phone', $normalizedPhone)
            ->when($exceptId !== null, fn (Builder $query) => $query->where('id', '!=', $exceptId))
            ->first();
    }

    private function contactData(GoogleContact $contact): array
    {
        return [
            'resource_name' => $contact->resource_name,
            'etag' => $contact->etag,
            'name' => $contact->name,
            'phone' => $contact->phone,
            'email' => $contact->email,
            'address' => $contact->address,
        ];
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
