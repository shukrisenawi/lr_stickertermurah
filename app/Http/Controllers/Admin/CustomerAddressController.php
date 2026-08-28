<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\User;
use App\Services\GoogleContactsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAddressController extends Controller
{
    private const MALAYSIAN_STATES = [
        'Johor',
        'Kedah',
        'Kelantan',
        'Melaka',
        'Negeri Sembilan',
        'Pahang',
        'Perak',
        'Perlis',
        'Pulau Pinang',
        'Sabah',
        'Sarawak',
        'Selangor',
        'Terengganu',
        'Kuala Lumpur',
        'Labuan',
        'Putrajaya',
    ];

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $tab = $request->string('tab')->toString();
        $sortColumns = [
            'recipient' => 'customer_addresses.recipient_name',
            'phone' => 'customer_addresses.no_hp',
            'address' => 'customer_addresses.address',
            'default' => 'customer_addresses.is_default',
            'updated' => 'customer_addresses.updated_at',
        ];
        $sort = $request->string('sort')->toString();
        if ($sort !== 'customer' && ! array_key_exists($sort, $sortColumns)) {
            $sort = 'updated';
        }

        $direction = $request->string('direction')->toString();
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $sort === 'updated' ? 'desc' : 'asc';
        }

        if (! in_array($tab, ['members', 'non-members', 'statistics'], true)) {
            $tab = 'members';
        }

        $addresses = null;
        if ($tab !== 'statistics') {
            $addresses = CustomerAddress::query()
                ->with('user:id,name,email,no_tel')
                ->when($tab === 'members', function (Builder $query): void {
                    $query->whereNotNull('user_id');
                })
                ->when($tab === 'non-members', function (Builder $query): void {
                    $query->whereNull('user_id');
                })
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $inner) use ($search): void {
                        $inner->where('recipient_name', 'like', '%'.$search.'%')
                            ->orWhere('no_hp', 'like', '%'.$search.'%')
                            ->orWhere('address', 'like', '%'.$search.'%')
                            ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                                $userQuery->where('name', 'like', '%'.$search.'%')
                                    ->orWhere('email', 'like', '%'.$search.'%')
                                    ->orWhere('no_tel', 'like', '%'.$search.'%');
                            });
                    });
                })
                ->when($sort === 'customer', function (Builder $query) use ($direction): void {
                    $query->orderBy(
                        User::query()
                            ->select('name')
                            ->whereColumn('users.id', 'customer_addresses.user_id'),
                        $direction,
                    );
                }, function (Builder $query) use ($sortColumns, $sort, $direction): void {
                    $query->orderBy($sortColumns[$sort], $direction);
                })
                ->orderByDesc('customer_addresses.id')
                ->paginate(20)
                ->withQueryString();
        }

        $statistics = $tab === 'statistics'
            ? $this->defaultAddressStatistics()
            : [
                'states' => [],
                'total_default_addresses' => 0,
                'classified_addresses' => 0,
                'unclassified_addresses' => 0,
            ];

        return Inertia::render('Admin/CustomerAddresses/Index', [
            'addresses' => $addresses,
            'search' => $search,
            'tab' => $tab,
            'sort' => $sort,
            'direction' => $direction,
            'statistics' => $statistics,
        ]);
    }

    /** @return array<string, mixed> */
    private function defaultAddressStatistics(): array
    {
        $stateCounts = array_fill_keys(self::MALAYSIAN_STATES, 0);
        $totalDefaultAddresses = 0;
        $classifiedAddresses = 0;

        CustomerAddress::query()
            ->where('is_default', true)
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->get(['address'])
            ->each(function (CustomerAddress $address) use (&$stateCounts, &$totalDefaultAddresses, &$classifiedAddresses): void {
                $totalDefaultAddresses++;
                $state = $this->extractAddressState($address->address);

                if ($state === null) {
                    return;
                }

                $stateCounts[$state]++;
                $classifiedAddresses++;
            });

        return [
            'states' => collect($stateCounts)
                ->filter(fn (int $count): bool => $count > 0)
                ->map(fn (int $count, string $state): array => [
                    'state' => $state,
                    'count' => $count,
                ])
                ->sortByDesc('count')
                ->values()
                ->all(),
            'total_default_addresses' => $totalDefaultAddresses,
            'classified_addresses' => $classifiedAddresses,
            'unclassified_addresses' => $totalDefaultAddresses - $classifiedAddresses,
        ];
    }

    public function repairAddresses(): RedirectResponse
    {
        $updatedCount = 0;
        $skippedDuplicateCount = 0;

        DB::transaction(function () use (&$updatedCount, &$skippedDuplicateCount): void {
            CustomerAddress::query()
                ->whereNotNull('address')
                ->where('address', '!=', '')
                ->get()
                ->each(function (CustomerAddress $address) use (&$updatedCount, &$skippedDuplicateCount): void {
                    $formattedAddress = $this->formatAddressUcwords($address->address);
                    if ($formattedAddress === $address->address) {
                        return;
                    }

                    $duplicateAddress = $address->user_id !== null
                        && CustomerAddress::query()
                            ->where('user_id', $address->user_id)
                            ->where('address', $formattedAddress)
                            ->whereKeyNot($address->id)
                            ->exists();

                    if ($duplicateAddress) {
                        $skippedDuplicateCount++;

                        return;
                    }

                    $address->update(['address' => $formattedAddress]);
                    $updatedCount++;
                });
        });

        $message = $updatedCount > 0
            ? $updatedCount.' alamat berjaya ditukar kepada format Ucwords.'
            : 'Semua alamat sudah dalam format Ucwords.';

        if ($skippedDuplicateCount > 0) {
            $duplicateMessage = $skippedDuplicateCount.' alamat tidak diubah kerana akan menjadi alamat pendua untuk user yang sama.';
            $message = $updatedCount > 0 ? $message.' '.$duplicateMessage : $duplicateMessage;
        }

        return back()->with('success', $message);
    }

    public function repairPhones(): RedirectResponse
    {
        $updatedCount = 0;

        DB::transaction(function () use (&$updatedCount): void {
            CustomerAddress::query()
                ->whereNotNull('no_hp')
                ->where('no_hp', '!=', '')
                ->get()
                ->each(function (CustomerAddress $address) use (&$updatedCount): void {
                    $formattedPhone = $this->formatPhoneNumber($address->no_hp);
                    if ($formattedPhone === $address->no_hp) {
                        return;
                    }

                    $address->update(['no_hp' => $formattedPhone]);
                    $updatedCount++;
                });
        });

        return back()->with('success', $updatedCount > 0
            ? $updatedCount.' no. telefon berjaya diformatkan.'
            : 'Semua no. telefon sudah dalam format yang betul.');
    }

    public function linkAddressesByPhone(Request $request, GoogleContactsService $googleContacts): RedirectResponse
    {
        $connection = $request->user()->googleContactConnection;

        if ($connection === null) {
            return back()->with('error', 'Sambungkan akaun Google sebelum memautkan alamat berdasarkan no. telefon.');
        }

        $contactsByPhone = [];
        foreach ($connection->contacts()->orderBy('id')->get(['name', 'normalized_phone', 'phone']) as $contact) {
            $phone = $googleContacts->normalizePhone((string) $contact->normalized_phone)
                ?? $googleContacts->normalizePhone((string) $contact->phone);

            if ($phone === null || blank($contact->name)) {
                continue;
            }

            $contactsByPhone[$phone] ??= $contact;
        }

        if ($contactsByPhone === []) {
            return back()->with('error', 'Tiada contact dengan no. telefon yang sah ditemui.');
        }

        $addresses = CustomerAddress::query()
            ->whereNull('user_id')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->get();

        if ($addresses->isEmpty()) {
            return back()->with('success', 'Tiada alamat yang belum dipautkan.');
        }

        $usersByPhone = [];
        foreach (User::query()->where('is_admin', false)->whereNotNull('no_tel')->get(['id', 'name', 'no_tel']) as $user) {
            $phone = $googleContacts->normalizePhone((string) $user->no_tel);

            if ($phone !== null) {
                $usersByPhone[$phone] ??= $user;
            }
        }

        $createdUserCount = 0;
        $linkedAddressCount = 0;
        $skippedAddressCount = 0;
        $temporaryPasswordHash = null;
        $blockedPhones = [];

        DB::transaction(function () use (
            $addresses,
            $contactsByPhone,
            $googleContacts,
            &$usersByPhone,
            &$createdUserCount,
            &$linkedAddressCount,
            &$skippedAddressCount,
            &$temporaryPasswordHash,
            &$blockedPhones,
        ): void {
            foreach ($addresses as $address) {
                $phone = $googleContacts->normalizePhone((string) $address->no_hp);

                if ($phone === null || ! isset($contactsByPhone[$phone])) {
                    continue;
                }

                if (isset($blockedPhones[$phone])) {
                    $skippedAddressCount++;

                    continue;
                }

                if (! isset($usersByPhone[$phone])) {
                    $contact = $contactsByPhone[$phone];
                    $customerName = $this->formatLinkedCustomerName((string) $contact->name);

                    if ($customerName === '') {
                        $blockedPhones[$phone] = true;
                        $skippedAddressCount++;

                        continue;
                    }

                    $existingNameUser = User::query()->where('name', $customerName)->first();
                    if ($existingNameUser !== null) {
                        if (! $existingNameUser->is_admin
                            && $googleContacts->normalizePhone((string) $existingNameUser->no_tel) === $phone) {
                            $usersByPhone[$phone] = $existingNameUser;
                        } else {
                            $blockedPhones[$phone] = true;
                            $skippedAddressCount++;

                            continue;
                        }
                    } else {
                        // Semua akaun batch bermula dengan password yang sama, jadi bcrypt hanya perlu dijalankan sekali.
                        $temporaryPasswordHash ??= Hash::make('123');
                        $usersByPhone[$phone] = User::query()->create([
                            'name' => $customerName,
                            'no_tel' => $phone,
                            'email' => null,
                            'password' => $temporaryPasswordHash,
                            'must_change_password' => true,
                            'is_admin' => false,
                        ]);
                        $createdUserCount++;
                    }
                }

                $user = $usersByPhone[$phone];
                $duplicateAddress = CustomerAddress::query()
                    ->where('user_id', $user->id)
                    ->where('address', $address->address)
                    ->whereKeyNot($address->id)
                    ->exists();

                if ($duplicateAddress) {
                    $skippedAddressCount++;

                    continue;
                }

                $hasDefaultAddress = CustomerAddress::query()
                    ->where('user_id', $user->id)
                    ->where('is_default', true)
                    ->exists();

                $address->update([
                    'user_id' => $user->id,
                    'is_default' => ! $hasDefaultAddress,
                ]);
                $linkedAddressCount++;
            }
        });

        if ($linkedAddressCount === 0) {
            return back()->with('error', $skippedAddressCount > 0
                ? $skippedAddressCount.' alamat sepadan tetapi tidak dapat dipautkan kerana nama user sudah digunakan atau alamat pendua.'
                : 'Tiada alamat yang sepadan dengan no. telefon dalam contact.');
        }

        $message = ($createdUserCount > 0 ? $createdUserCount.' user baharu dan ' : '')
            .$linkedAddressCount.' alamat berjaya dipautkan berdasarkan no. telefon.';

        if ($skippedAddressCount > 0) {
            $message .= ' '.$skippedAddressCount.' alamat tidak dipautkan kerana nama user sudah digunakan atau alamat pendua.';
        }

        return back()->with('success', $message);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/CustomerAddresses/Form', [
            'address' => null,
            'customers' => $this->customerOptions(),
            'tab' => $this->normalizeTab($request->string('tab')->toString()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);
        $data = $this->addressData($validated);

        $address = DB::transaction(function () use ($data): CustomerAddress {
            if ($data['user_id'] !== null && $data['is_default']) {
                $this->clearDefault($data['user_id']);
            }

            return CustomerAddress::query()->create($data);
        });

        return redirect()
            ->route('admin.customer-addresses.index', ['tab' => $this->tabForUser($address->user_id)])
            ->with('success', 'Customer address berjaya ditambah.');
    }

    public function edit(Request $request, CustomerAddress $customerAddress): Response
    {
        return Inertia::render('Admin/CustomerAddresses/Form', [
            'address' => [
                'id' => $customerAddress->id,
                'user_id' => $customerAddress->user_id,
                'recipient_name' => $customerAddress->recipient_name,
                'address' => $customerAddress->address,
                'no_hp' => $customerAddress->no_hp,
                'is_default' => $customerAddress->is_default,
            ],
            'customers' => $this->customerOptions(),
            'tab' => $this->normalizeTab($request->string('tab')->toString()),
        ]);
    }

    public function update(Request $request, CustomerAddress $customerAddress): RedirectResponse
    {
        $validated = $this->validateAddress($request, $customerAddress);
        $data = $this->addressData($validated);
        $oldUserId = $customerAddress->user_id;
        $wasDefault = $customerAddress->is_default;

        DB::transaction(function () use ($customerAddress, $data, $oldUserId, $wasDefault): void {
            if ($data['user_id'] !== null && $data['is_default']) {
                $this->clearDefault($data['user_id'], $customerAddress->id);
            }

            $customerAddress->update($data);

            if ($wasDefault && $oldUserId !== null && $oldUserId !== $data['user_id']) {
                $this->ensureDefault($oldUserId);
            }
        });

        return redirect()
            ->route('admin.customer-addresses.index', ['tab' => $this->tabForUser($data['user_id'])])
            ->with('success', 'Customer address berjaya dikemaskini.');
    }

    public function destroy(CustomerAddress $customerAddress): RedirectResponse
    {
        $userId = $customerAddress->user_id;
        $wasDefault = $customerAddress->is_default;

        DB::transaction(function () use ($customerAddress, $userId, $wasDefault): void {
            $customerAddress->delete();

            if ($wasDefault && $userId !== null) {
                $this->ensureDefault($userId);
            }
        });

        return redirect()
            ->route('admin.customer-addresses.index', ['tab' => $this->tabForUser($userId)])
            ->with('success', 'Customer address berjaya dipadam.');
    }

    private function customerOptions()
    {
        return User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'no_tel']);
    }

    /** @return array<string, mixed> */
    private function validateAddress(Request $request, ?CustomerAddress $address = null): array
    {
        $userId = $request->input('user_id');
        $rules = [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ];

        if ($userId !== null && $userId !== '') {
            $uniqueAddress = Rule::unique('customer_addresses', 'address')
                ->where('user_id', (int) $userId);

            if ($address) {
                $uniqueAddress->ignore($address->id);
            }

            $rules['address'][] = $uniqueAddress;
        }

        return $request->validate($rules);
    }

    /** @param array<string, mixed> $validated */
    private function addressData(array $validated): array
    {
        return [
            'user_id' => empty($validated['user_id']) ? null : (int) $validated['user_id'],
            'recipient_name' => $validated['recipient_name'],
            'address' => $validated['address'],
            'no_hp' => $validated['no_hp'] ?? null,
            'is_default' => ! empty($validated['user_id']) && (bool) ($validated['is_default'] ?? false),
        ];
    }

    private function clearDefault(int $userId, ?int $exceptId = null): void
    {
        CustomerAddress::query()
            ->where('user_id', $userId)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }

    private function ensureDefault(int $userId): void
    {
        if (CustomerAddress::query()->where('user_id', $userId)->where('is_default', true)->exists()) {
            return;
        }

        CustomerAddress::query()
            ->where('user_id', $userId)
            ->latest('updated_at')
            ->first()?->update(['is_default' => true]);
    }

    private function normalizeTab(string $tab): string
    {
        return in_array($tab, ['members', 'non-members'], true) ? $tab : 'members';
    }

    private function tabForUser(?int $userId): string
    {
        return $userId === null ? 'non-members' : 'members';
    }

    private function formatAddressUcwords(?string $address): string
    {
        $address = preg_replace('/[ \t]+/', ' ', trim((string) $address)) ?? trim((string) $address);
        $address = preg_replace('/\s*,\s*/u', ', ', $address) ?? $address;
        $address = $this->trimAddressAfterStatePostcode($address);
        $address = rtrim($address);

        return Str::title(mb_strtolower($address));
    }

    private function trimAddressAfterStatePostcode(string $address): string
    {
        $pattern = '/^(.*?\b(?:'.$this->malaysianStatePattern().'))\s*,\s*[0-9]{5}\b.*$/isu';

        if (! preg_match($pattern, $address, $matches)) {
            return $address;
        }

        return trim($matches[1]);
    }

    private function extractAddressState(?string $address): ?string
    {
        $pattern = '/(?<!\p{L})('.$this->malaysianStatePattern().')(?!\p{L})/iu';
        if (preg_match_all($pattern, (string) $address, $matches) < 1) {
            return null;
        }

        $matchedStates = $matches[1] ?? [];
        $matchedState = end($matchedStates);
        if (! is_string($matchedState)) {
            return null;
        }

        $normalizedMatchedState = preg_replace('/\s+/', ' ', mb_strtolower(trim($matchedState)))
            ?? mb_strtolower(trim($matchedState));

        foreach (self::MALAYSIAN_STATES as $state) {
            if (mb_strtolower($state) === $normalizedMatchedState) {
                return $state;
            }
        }

        return null;
    }

    private function malaysianStatePattern(): string
    {
        return implode('|', array_map(
            static fn (string $state): string => preg_quote($state, '/'),
            self::MALAYSIAN_STATES,
        ));
    }

    private function formatPhoneNumber(?string $phone): string
    {
        $original = trim((string) $phone);
        $digits = preg_replace('/\D+/', '', $original) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '60')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '6')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 11) {
            return substr($digits, 0, 3).'-'.substr($digits, 3, 4).' '.substr($digits, 7, 4);
        }

        if (strlen($digits) === 10) {
            return substr($digits, 0, 3).'-'.substr($digits, 3, 3).' '.substr($digits, 6, 4);
        }

        return $original;
    }

    private function formatLinkedCustomerName(string $name): string
    {
        $name = preg_replace('/^sc\s+/iu', '', trim($name)) ?? trim($name);
        $name = preg_replace('/[ \t]+/', ' ', $name) ?? $name;

        return Str::title(mb_strtolower(trim($name)));
    }
}
