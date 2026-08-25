<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\GoogleContact;
use App\Models\GoogleContactConnection;
use App\Models\User;
use App\Services\GoogleContactsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ContactExtractionController extends Controller
{
    public function index(Request $request): Response
    {
        $request->session()->forget('contact_extract.raw_text');

        return Inertia::render('Admin/Contacts/Extract', [
            'rawText' => '',
            'contacts' => [],
            'duplicateCustomer' => null,
        ]);
    }

    public function extract(Request $request): Response
    {
        $validated = $request->validate([
            'raw_text' => ['required', 'string'],
        ]);

        $rawText = trim((string) $validated['raw_text']);
        $request->session()->put('contact_extract.raw_text', $rawText);

        try {
            $contacts = $this->buildContactsWithSuggestions($rawText);
            $duplicateCustomer = $this->findDuplicateCustomer($contacts);
            $swalError = empty($contacts)
                ? 'Tiada maklumat contact yang boleh diekstrak daripada teks tersebut. Sila semak format dan cuba lagi.'
                : null;
        } catch (Throwable $exception) {
            report($exception);
            $contacts = [];
            $duplicateCustomer = null;
            $swalError = 'Proses extract gagal. Sila semak teks dan cuba lagi.';
        }

        return Inertia::render('Admin/Contacts/Extract', [
            'rawText' => $rawText,
            'contacts' => $contacts,
            'duplicateCustomer' => $duplicateCustomer,
            'phoneConflict' => null,
            'duplicateError' => null,
            'swalError' => $swalError,
        ]);
    }

    public function addAddress(Request $request): Response
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'postcode' => ['nullable', 'string'],
            'make_default' => ['sometimes', 'boolean'],
            'redirect_to_project' => ['sometimes', 'boolean'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if ($phone === null) {
            return $this->renderExtractPage($request, [
                'swalError' => 'Nombor telefon tidak sah.',
            ]);
        }

        $addressText = $this->formatSavedAddress($validated['address']);
        $userId = (int) $validated['user_id'];
        $existingAddress = CustomerAddress::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(address) = ?', [mb_strtolower($addressText)])
            ->first();

        if ($existingAddress !== null) {
            return $this->renderExtractPage($request, [
                'duplicateError' => 'Alamat ini sudah wujud untuk user yang dipilih. Sila semak alamat sedia ada dalam popup.',
            ]);
        }

        $makeDefault = $request->boolean('make_default');
        $address = DB::transaction(function () use ($userId, $addressText, $validated, $phone, $makeDefault): CustomerAddress {
            $hasAddresses = CustomerAddress::query()->where('user_id', $userId)->exists();

            if ($makeDefault) {
                CustomerAddress::query()
                    ->where('user_id', $userId)
                    ->update(['is_default' => false]);
            }

            return CustomerAddress::query()->create([
                'user_id' => $userId,
                'address' => $addressText,
                'recipient_name' => $this->formatCustomerName($validated['name']),
                'no_hp' => $phone,
                'is_default' => $makeDefault || ! $hasAddresses,
            ]);
        });

        if ($request->boolean('redirect_to_project')) {
            return $this->renderExtractPage($request, [
                'success' => 'Alamat berjaya ditambah pada user yang dipilih.',
                'successType' => 'address',
                'createdUserId' => (int) $validated['user_id'],
                'createdAddressId' => (int) $address->id,
                'redirectTo' => 'project',
            ]);
        }

        return $this->renderExtractPage($request)
            ->with('success', 'Alamat berjaya ditambah pada pengguna yang dipilih.');
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $search = trim($request->string('q')->toString());
        if (mb_strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $like = '%'.$search.'%';
        $digits = preg_replace('/\D+/', '', $search) ?? '';
        $phoneCandidates = array_values(array_unique(array_filter([
            $digits,
            str_starts_with($digits, '0') ? '60'.substr($digits, 1) : null,
            str_starts_with($digits, '60') ? '0'.substr($digits, 2) : null,
        ], fn (?string $value): bool => $value !== null && strlen($value) >= 2)));
        $customers = User::query()
            ->where('is_admin', false)
            ->with(['customerAddresses' => function ($query): void {
                $query->orderByDesc('is_default')->orderByDesc('updated_at');
            }])
            ->where(function ($query) use ($like, $phoneCandidates): void {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);

                foreach ($phoneCandidates as $phoneCandidate) {
                    $query->orWhere('no_tel', 'like', '%'.$phoneCandidate.'%');
                }
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email', 'no_tel']);

        return response()->json([
            'results' => $customers->map(fn (User $customer): array => [
                'id' => (int) $customer->id,
                'name' => (string) $customer->name,
                'email' => $customer->email,
                'no_tel' => $customer->no_tel,
                'addresses' => $customer->customerAddresses->map(fn (CustomerAddress $address): array => [
                    'id' => (int) $address->id,
                    'recipient_name' => $address->recipient_name,
                    'address' => $address->address,
                    'no_hp' => $address->no_hp,
                    'is_default' => (bool) $address->is_default,
                ])->values()->all(),
            ])->values(),
        ]);
    }

    public function addUser(Request $request, GoogleContactsService $googleContacts): Response
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'postcode' => ['nullable', 'string'],
            'force_address' => ['sometimes', 'boolean'],
            'redirect_to_order' => ['sometimes', 'boolean'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if ($phone === null) {
            return $this->renderExtractPage($request, [
                'swalError' => 'Nombor telefon tidak sah.',
            ]);
        }

        $customerName = $this->formatCustomerName($validated['name']);
        $addressText = $this->formatSavedAddress($validated['address']);
        $forceAddress = $request->boolean('force_address');
        $redirectToOrder = $request->boolean('redirect_to_order');
        $existingUser = $this->findUserByPhone($phone);
        $existingAddress = $this->findAddressByPhoneAndAddress($phone, $addressText);

        if ($existingAddress !== null) {
            $message = 'Data sama dah wujud. Alamat tidak ditambah semula.';

            return $this->renderExtractPage($request, [
                'duplicateError' => $message,
            ]);
        }

        if ($existingUser !== null) {
            if (! $forceAddress) {
                return $this->renderExtractPage($request, [
                    'phoneConflict' => [
                        'user_id' => (int) $existingUser->id,
                        'user_name' => (string) $existingUser->name,
                        'name' => (string) $validated['name'],
                        'phone' => (string) $validated['phone'],
                        'address' => $addressText,
                        'postcode' => (string) ($validated['postcode'] ?? '-'),
                    ],
                ]);
            }

            DB::transaction(function () use ($existingUser, $customerName, $addressText, $phone): void {
                $hasAddresses = CustomerAddress::query()
                    ->where('user_id', $existingUser->id)
                    ->exists();

                CustomerAddress::query()->create([
                    'user_id' => $existingUser->id,
                    'recipient_name' => $customerName,
                    'address' => $addressText,
                    'no_hp' => $phone,
                    'is_default' => ! $hasAddresses,
                ]);
            });

            return $this->renderExtractPage($request)
                ->with('success', 'Alamat berjaya ditambah pada customer sedia ada.');
        }

        if (User::query()->where('name', $customerName)->exists()) {
            $message = 'Nama customer sudah wujud. Sila semak contact yang hendak ditambah.';

            return $this->renderExtractPage($request, [
                'swalError' => $message,
            ]);
        }

        [$user, $customerAddress] = DB::transaction(function () use ($customerName, $addressText, $phone): array {
            $user = User::query()->create([
                'name' => $customerName,
                'no_tel' => $phone,
                'email' => null,
                'password' => Hash::make('123'),
                'must_change_password' => true,
                'is_admin' => false,
            ]);

            $customerAddress = CustomerAddress::query()->create([
                'user_id' => $user->id,
                'recipient_name' => $customerName,
                'address' => $addressText,
                'no_hp' => $phone,
                'is_default' => true,
            ]);

            return [$user, $customerAddress];
        });

        $successMessage = "Customer {$user->name} berjaya dicipta. Alamat berjaya disimpan.";
        $connection = $request->user()->googleContactConnection;
        if ($connection !== null) {
            try {
                $successMessage .= $this->addGoogleContact($googleContacts, $connection, $user, $phone, $addressText)
                    ? ' Contact Google berjaya ditambah.'
                    : ' Contact Google sedia ada, tidak ditambah semula.';
            } catch (Throwable $exception) {
                report($exception);
                $successMessage .= ' Namun contact Google gagal ditambah.';
            }
        }

        return $this->renderExtractPage($request, [
            'success' => $successMessage,
            'successType' => 'customer',
            'createdUserId' => (int) $user->id,
            'createdAddressId' => (int) $customerAddress->id,
            'redirectTo' => $redirectToOrder ? 'order' : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function renderExtractPage(Request $request, array $extra = []): Response
    {
        $rawText = (string) $request->session()->get('contact_extract.raw_text', '');

        return Inertia::render('Admin/Contacts/Extract', array_merge([
            'rawText' => $rawText,
            'contacts' => $this->buildContactsWithSuggestions($rawText),
            'duplicateCustomer' => null,
            'phoneConflict' => null,
            'duplicateError' => null,
            'swalError' => null,
            'redirectTo' => null,
        ], $extra));
    }

    /**
     * @param  array<int, array{name:string,phone:string,address:string,postcode:string,suggestions:array<int, array{id:int,name:string,email:string|null,latest_address:string,score:int,is_exact:bool}>}>  $contacts
     * @return array{contact:array{name:string,phone:string,address:string,postcode:string},customer:array{id:int,name:string,email:string|null,no_tel:string|null,addresses:array<int,array{id:int,recipient_name:string|null,address:string,no_hp:string|null,is_default:bool}>}}|null
     */
    private function findDuplicateCustomer(array $contacts): ?array
    {
        foreach ($contacts as $contact) {
            $exactSuggestion = collect($contact['suggestions'] ?? [])->first(
                fn (array $suggestion): bool => ($suggestion['is_exact'] ?? false) === true,
            );

            if (! is_array($exactSuggestion)) {
                continue;
            }

            $customer = User::query()
                ->whereKey((int) $exactSuggestion['id'])
                ->where('is_admin', false)
                ->with(['customerAddresses' => function ($query): void {
                    $query->orderByDesc('is_default')->orderByDesc('updated_at');
                }])
                ->first();

            if (! $customer) {
                continue;
            }

            return [
                'contact' => [
                    'name' => (string) $contact['name'],
                    'phone' => (string) $contact['phone'],
                    'address' => (string) $contact['address'],
                    'postcode' => (string) $contact['postcode'],
                ],
                'customer' => [
                    'id' => (int) $customer->id,
                    'name' => (string) $customer->name,
                    'email' => $customer->email,
                    'no_tel' => $customer->no_tel,
                    'addresses' => $customer->customerAddresses->map(fn (CustomerAddress $address): array => [
                        'id' => (int) $address->id,
                        'recipient_name' => $address->recipient_name,
                        'address' => $address->address,
                        'no_hp' => $address->no_hp,
                        'is_default' => (bool) $address->is_default,
                    ])->values()->all(),
                ],
            ];
        }

        return null;
    }

    private function findUserByPhone(string $phone): ?User
    {
        $user = User::query()
            ->where('is_admin', false)
            ->where('no_tel', $phone)
            ->first();

        if ($user !== null) {
            return $user;
        }

        $user = User::query()
            ->where('is_admin', false)
            ->get()
            ->first(fn (User $candidate): bool => $this->normalizePhone($candidate->no_tel) === $phone);

        if ($user !== null) {
            return $user;
        }

        return CustomerAddress::query()
            ->with('user')
            ->whereNotNull('user_id')
            ->get()
            ->first(function (CustomerAddress $address) use ($phone): bool {
                return $address->user !== null
                    && ! $address->user->is_admin
                    && (($this->normalizePhone($address->no_hp) ?? $this->normalizePhone($address->user->no_tel)) === $phone);
            })?->user;
    }

    private function findAddressByPhoneAndAddress(string $phone, string $address): ?CustomerAddress
    {
        $normalizedAddress = $this->normalizeAddress($address);

        return CustomerAddress::query()
            ->with('user')
            ->get()
            ->first(function (CustomerAddress $candidate) use ($phone, $normalizedAddress): bool {
                if ($candidate->user?->is_admin) {
                    return false;
                }

                $candidatePhone = $this->normalizePhone($candidate->no_hp)
                    ?? $this->normalizePhone($candidate->user?->no_tel);

                return $candidatePhone === $phone
                    && $this->normalizeAddress((string) $candidate->address) === $normalizedAddress;
            });
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

    private function formatExtractedPhone(string $phone): string
    {
        $original = trim($phone);
        $digits = preg_replace('/\D+/', '', $original) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '60')) {
            $digits = '0'.substr($digits, 2);
        }

        if (! str_starts_with($digits, '0')) {
            return $original;
        }

        if (strlen($digits) === 11) {
            return substr($digits, 0, 3).'-'.substr($digits, 3, 4).' '.substr($digits, 7);
        }

        if (strlen($digits) === 10) {
            return substr($digits, 0, 3).'-'.substr($digits, 3, 3).' '.substr($digits, 6);
        }

        return $original;
    }

    private function normalizeAddress(string $address): string
    {
        $address = $this->toUpperAscii($address);
        $address = preg_replace('/\s+/', ' ', $address) ?? $address;

        return trim($address);
    }

    private function formatCustomerName(string $name): string
    {
        $name = preg_replace('/\b(?:bin|binti)\b/iu', ' ', trim($name)) ?? trim($name);
        $name = preg_replace('/^sc\s+/iu', '', $name) ?? $name;
        $name = trim($name);

        return $this->formatSavedName($name);
    }

    private function formatGoogleContactName(string $name): string
    {
        return 'Sc '.$this->formatCustomerName($name);
    }

    private function formatSavedName(string $value): string
    {
        $value = preg_replace('/[ \t]+/', ' ', trim($value)) ?? trim($value);

        return Str::title(mb_strtolower($value));
    }

    private function formatSavedAddress(string $value): string
    {
        $value = preg_replace('/[ \t]+/', ' ', trim($value)) ?? trim($value);
        $value = mb_strtolower($value);

        return preg_replace_callback(
            '/(^|\R)([ \t]*)(\p{L})/u',
            fn (array $matches): string => $matches[1].$matches[2].mb_strtoupper($matches[3]),
            $value,
        ) ?? $value;
    }

    /**
     * @return array<int, array{name: string, phone: string, address: string, postcode: string, suggestions: array<int, array{id:int,name:string,email:string|null,latest_address:string,score:int,is_exact:bool}>}>
     */
    private function buildContactsWithSuggestions(string $rawText): array
    {
        if (trim($rawText) === '') {
            return [];
        }

        $contacts = $this->parseContacts($rawText);
        $users = User::query()
            ->where('is_admin', false)
            ->select(['id', 'name', 'email'])
            ->with('defaultCustomerAddress')
            ->get();

        foreach ($contacts as &$contact) {
            $contact['suggestions'] = $this->findSimilarUsers($contact['name'], $users->all());
        }

        unset($contact);

        return $contacts;
    }

    /**
     * @return array<int, array{name: string, phone: string, address: string, postcode: string}>
     */
    private function parseContacts(string $rawText): array
    {
        $fromAi = $this->parseContactsWithAi($rawText);
        if (! empty($fromAi)) {
            return $fromAi;
        }

        $lines = preg_split('/\R+/', $rawText) ?: [];
        $contacts = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line, 3));
            if (count($parts) !== 3) {
                continue;
            }

            [$name, $phone, $address] = $parts;
            $name = $this->toUpperAscii($name);
            $phone = $this->formatExtractedPhone($this->toUpperAscii($phone));
            $address = $this->toUpperAscii($address);

            $contacts[] = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'postcode' => $this->extractPostcode($address),
            ];
        }

        if (! empty($contacts)) {
            return $contacts;
        }

        $phoneIndexes = [];
        foreach ($lines as $index => $line) {
            if ($this->looksLikePhone($line)) {
                $phoneIndexes[] = $index;
            }
        }

        foreach ($phoneIndexes as $phonePosition => $phoneIndex) {
            $name = $lines[$phoneIndex - 1] ?? '';
            $hasNextPhone = isset($phoneIndexes[$phonePosition + 1]);
            $nextPhoneIndex = $phoneIndexes[$phonePosition + 1] ?? count($lines);
            $addressLength = $hasNextPhone
                ? max(0, $nextPhoneIndex - $phoneIndex - 2)
                : max(0, count($lines) - $phoneIndex - 1);
            $addressLines = array_slice($lines, $phoneIndex + 1, $addressLength);

            if ($name === '' || empty($addressLines)) {
                continue;
            }

            $address = $this->toUpperAscii(implode(' ', $addressLines));

            $contacts[] = [
                'name' => $this->toUpperAscii($name),
                'phone' => $this->formatExtractedPhone($this->toUpperAscii($lines[$phoneIndex])),
                'address' => $address,
                'postcode' => $this->extractPostcode($address),
            ];
        }

        return $contacts;
    }

    /**
     * @return array<int, array{name: string, phone: string, address: string, postcode: string}>
     */
    private function parseContactsWithAi(string $rawText): array
    {
        $apiKey = (string) config('services.sumopod.api_key', '');
        $endpoint = (string) config('services.sumopod.endpoint', 'https://ai.sumopod.com/v1/chat/completions');
        $model = (string) config('services.sumopod.model', 'gpt-5.6-luna');

        if ($apiKey === '') {
            return [];
        }

        $prompt = "Extract every customer contact from the text below. Return ONLY a valid JSON array, with no markdown or explanation.\n"
            ."Each item must contain exactly these keys: name, phone, address, postcode.\n"
            ."Keep the complete name, phone number, and address. Do not merge separate contacts.\n"
            ."Use '-' for postcode when it is not available. Ignore lines that do not contain a usable contact.\n\n"
            .$rawText;

        try {
            $response = Http::connectTimeout(10)
                ->timeout(60)
                ->withToken($apiKey)
                ->acceptJson()
                ->post($endpoint, [
                    'model' => $model,
                    'max_tokens' => 2000,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You extract structured Malaysian customer contacts accurately and return strict JSON only.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                return [];
            }

            $content = (string) data_get($response->json(), 'choices.0.message.content', '');
            if ($content === '') {
                return [];
            }

            $json = trim($content);
            if (str_starts_with($json, '```')) {
                $json = preg_replace('/^```(?:json)?\s*/', '', $json) ?? $json;
                $json = preg_replace('/\s*```$/', '', $json) ?? $json;
            }

            $jsonStart = strpos($json, '[');
            $jsonEnd = strrpos($json, ']');
            if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd >= $jsonStart) {
                $json = substr($json, $jsonStart, $jsonEnd - $jsonStart + 1);
            }

            $decoded = json_decode($json, true);
            if (is_array($decoded) && isset($decoded['contacts']) && is_array($decoded['contacts'])) {
                $decoded = $decoded['contacts'];
            }

            if (! is_array($decoded)) {
                return [];
            }

            $contacts = [];
            foreach ($decoded as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $name = $this->toUpperAscii((string) ($row['name'] ?? ''));
                $phone = $this->formatExtractedPhone($this->toUpperAscii((string) ($row['phone'] ?? '')));
                $address = $this->toUpperAscii((string) ($row['address'] ?? ''));
                $postcode = $this->toUpperAscii((string) ($row['postcode'] ?? ''));

                if ($name === '' || $phone === '' || $address === '') {
                    continue;
                }

                if (! preg_match('/^\d{5}$/', $postcode)) {
                    $postcode = $this->extractPostcode($address);
                }

                $contacts[] = [
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address,
                    'postcode' => $postcode,
                ];
            }

            return $contacts;
        } catch (Throwable) {
            return [];
        }
    }

    private function looksLikePhone(string $value): bool
    {
        if (preg_match('/\p{L}/u', $value) === 1) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return preg_match('/^(?:0[1-9]\d{7,9}|60[1-9]\d{7,9})$/', $digits) === 1;
    }

    private function toUpperAscii(string $value): string
    {
        $map = [
            "\xE2\x80\x98" => "'",
            "\xE2\x80\x99" => "'",
            "\xE2\x80\x9C" => '"',
            "\xE2\x80\x9D" => '"',
            "\xE2\x80\x93" => '-',
            "\xE2\x80\x94" => '-',
        ];

        $value = strtr($value, $map);
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }

        return mb_strtoupper(trim($value));
    }

    private function extractPostcode(string $address): string
    {
        if (preg_match('/\b(\d{5})\b/', $address, $matches) === 1) {
            return $matches[1];
        }

        return '-';
    }

    /**
     * @param  array<int, object{id:int, name:string, email:string}>  $users
     * @return array<int, array{id:int,name:string,email:string,latest_address:string,score:int}>
     */
    private function findSimilarUsers(string $contactName, array $users): array
    {
        $target = $this->normalizeName($contactName);
        if ($target === '') {
            return [];
        }

        $targetTokens = array_values(array_filter(explode(' ', $target)));
        $results = [];

        foreach ($users as $user) {
            $candidate = $this->normalizeName($user->name);
            if ($candidate === '') {
                continue;
            }

            similar_text($target, $candidate, $similarPercent);
            $distance = levenshtein($target, $candidate);
            $candidateTokens = array_values(array_filter(explode(' ', $candidate)));
            $commonTokens = count(array_intersect($targetTokens, $candidateTokens));
            $maxTokenCount = max(count($targetTokens), count($candidateTokens), 1);
            $tokenRatio = $commonTokens / $maxTokenCount;

            $score = 0;

            if ($target === $candidate) {
                $score += 100;
            }

            if (str_contains($candidate, $target) || str_contains($target, $candidate)) {
                $score += 25;
            }

            $score += (int) round($similarPercent * 0.6);
            $score += (int) round($tokenRatio * 35);
            $score += max(0, 25 - min($distance, 25));

            if ($score >= 40) {
                $latestAddress = $user->defaultCustomerAddress?->address;

                $results[] = [
                    'id' => (int) $user->id,
                    'name' => $this->toUpperAscii($user->name),
                    'email' => $user->email,
                    'latest_address' => $latestAddress !== null && trim((string) $latestAddress) !== ''
                        ? $this->toUpperAscii((string) $latestAddress)
                        : '-',
                    'score' => $score,
                    'is_exact' => $target === $candidate,
                ];
            }
        }

        usort($results, function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, 6);
    }

    private function normalizeName(string $name): string
    {
        $upper = $this->toUpperAscii($name);
        $upper = preg_replace('/\b(?:BIN|BINTI)\b/', ' ', $upper) ?? $upper;
        $upper = preg_replace('/^SC\s+/', '', $upper) ?? $upper;
        $upper = preg_replace('/[^A-Z0-9 ]+/', ' ', $upper) ?? '';
        $upper = preg_replace('/\s+/', ' ', $upper) ?? '';

        return trim($upper);
    }

    private function addGoogleContact(
        GoogleContactsService $googleContacts,
        GoogleContactConnection $connection,
        User $user,
        string $phone,
        string $address,
    ): bool {
        $normalizedPhone = $googleContacts->normalizePhone($phone);
        if ($normalizedPhone === null) {
            return false;
        }

        if ($connection->contacts()->where('normalized_phone', $normalizedPhone)->exists()) {
            return false;
        }

        $result = $googleContacts->createContact(
            $connection,
            $this->formatGoogleContactName($user->name),
            $phone,
            null,
            $address,
        );
        $contact = $result['contact'];

        GoogleContact::query()->updateOrCreate(
            [
                'google_contact_connection_id' => $connection->id,
                'resource_name' => $contact['resource_name'],
            ],
            [
                'etag' => $contact['etag'],
                'name' => $contact['name'],
                'normalized_phone' => $normalizedPhone,
                'phone' => $contact['phone'],
                'email' => $contact['email'],
                'address' => $contact['address'],
            ],
        );

        return true;
    }
}
