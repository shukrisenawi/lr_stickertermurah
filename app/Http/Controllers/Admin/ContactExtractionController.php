<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ContactExtractionController extends Controller
{
    public function index(Request $request): View
    {
        $request->session()->forget('contact_extract.raw_text');

        return view('admin.contacts.extract', [
            'rawText' => '',
            'contacts' => [],
        ]);
    }

    public function extract(Request $request): View
    {
        $validated = $request->validate([
            'raw_text' => ['required', 'string'],
        ]);

        $rawText = trim((string) $validated['raw_text']);
        $request->session()->put('contact_extract.raw_text', $rawText);

        return view('admin.contacts.extract', [
            'rawText' => $rawText,
            'contacts' => $this->buildContactsWithSuggestions($rawText),
        ]);
    }

    public function addAddress(Request $request): View
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'postcode' => ['nullable', 'string'],
        ]);

        $address = CustomerAddress::query()->firstOrNew([
            'user_id' => (int) $validated['user_id'],
            'address' => $validated['address'],
        ]);

        $address->no_hp = $validated['phone'];
        $address->save();

        $rawText = (string) $request->session()->get('contact_extract.raw_text', '');

        return view('admin.contacts.extract', [
            'rawText' => $rawText,
            'contacts' => $this->buildContactsWithSuggestions($rawText),
        ])->with('success', 'Alamat berjaya ditambah pada pengguna yang dipilih.');
    }
    public function addGoogleContact(Request $request): View
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $rawText = (string) $request->session()->get('contact_extract.raw_text', '');
        $token = (string) $request->session()->get('admin.google_contacts.token', '');

        if ($token === '') {
            return view('admin.contacts.extract', [
                'rawText' => $rawText,
                'contacts' => $this->buildContactsWithSuggestions($rawText),
                'swalError' => 'Google Contact belum disambungkan. Sila sambung dahulu di menu Contact.',
            ]);
        }

        $name = $this->formatGoogleContactName((string) $validated['name']);
        $phone = $this->normalizeGooglePhone((string) $validated['phone']);

        if ($phone === '') {
            return view('admin.contacts.extract', [
                'rawText' => $rawText,
                'contacts' => $this->buildContactsWithSuggestions($rawText),
                'swalError' => 'No HP tidak sah untuk disimpan ke Google Contact.',
            ]);
        }

        try {
            $hasDuplicate = $this->hasGoogleContactDuplicate(
                $token,
                (string) $validated['name'],
                $name,
                $phone
            );
        } catch (Throwable) {
            return view('admin.contacts.extract', [
                'rawText' => $rawText,
                'contacts' => $this->buildContactsWithSuggestions($rawText),
                'swalError' => 'Gagal semak data Google Contact. Sila cuba semula.',
            ]);
        }

        if ($hasDuplicate) {
            return view('admin.contacts.extract', [
                'rawText' => $rawText,
                'contacts' => $this->buildContactsWithSuggestions($rawText),
                'swalError' => 'Nama atau No HP sudah wujud dalam Google Contact. Data tidak disimpan.',
            ]);
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post('https://people.googleapis.com/v1/people:createContact', [
                'names' => [
                    [
                        'displayName' => $name,
                    ],
                ],
                'phoneNumbers' => [
                    [
                        'value' => $phone,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            if ($response->status() === 401 || $response->status() === 403) {
                $request->session()->forget('admin.google_contacts.token');
            }

            return view('admin.contacts.extract', [
                'rawText' => $rawText,
                'contacts' => $this->buildContactsWithSuggestions($rawText),
                'swalError' => 'Gagal tambah contact ke Google. Sila sambung semula akaun Google Contact.',
            ]);
        }

        return view('admin.contacts.extract', [
            'rawText' => $rawText,
            'contacts' => $this->buildContactsWithSuggestions($rawText),
        ])->with('success', 'Contact Google berjaya ditambah.');
    }

    public function addUser(Request $request): View
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'postcode' => ['nullable', 'string'],
        ]);

        $name = $validated['name'];
        $rawText = (string) $request->session()->get('contact_extract.raw_text', '');

        $existingUser = User::query()
            ->where('name', $name)
            ->first();

        if ($existingUser !== null) {
            $swalError = 'Nama pengguna sudah wujud. Sila pilih pengguna sedia ada pada senarai padanan.';

            return view('admin.contacts.extract', [
                'rawText' => $rawText,
                'contacts' => $this->buildContactsWithSuggestions($rawText),
                'swalError' => $swalError,
            ]);
        }

        $email = $this->generateImportEmail($name);

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'is_admin' => false,
        ]);

        CustomerAddress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'address' => $validated['address'],
            ],
            [
                'no_hp' => $validated['phone'],
            ]
        );

        return view('admin.contacts.extract', [
            'rawText' => $rawText,
            'contacts' => $this->buildContactsWithSuggestions($rawText),
        ])->with('success', 'Pengguna baru dan alamat berjaya ditambah.');
    }

    /**
     * @return array<int, array{name: string, phone: string, address: string, postcode: string, suggestions: array<int, array{id:int,name:string,email:string,latest_address:string,score:int}>}>
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
        $fromAi = $this->parseContactsWithOpenAi($rawText);
        if (!empty($fromAi)) {
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
            $phone = $this->toUpperAscii($phone);
            $address = $this->toUpperAscii($address);

            $contacts[] = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'postcode' => $this->extractPostcode($address),
            ];
        }

        return $contacts;
    }

    /**
     * @return array<int, array{name: string, phone: string, address: string, postcode: string}>
     */
    private function parseContactsWithOpenAi(string $rawText): array
    {
        $apiKey = (string) config('services.openai.api_key', '');
        $model = (string) config('services.openai.model', 'gpt-4o');

        if ($apiKey === '') {
            return [];
        }

        $prompt = "Extract contact rows from this text. Return ONLY valid JSON array.\n"
            . "Each item must contain keys: name, phone, address, postcode.\n"
            . "All values must be uppercase.\n"
            . "If postcode missing, set as '-'.\n"
            . "Ignore invalid lines.\n\n"
            . $rawText;

        try {
            $response = Http::timeout(45)
                ->withToken($apiKey)
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => 0,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You extract structured contacts accurately and return strict JSON only.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
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

            $decoded = json_decode($json, true);
            if (!is_array($decoded)) {
                return [];
            }

            $contacts = [];
            foreach ($decoded as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $name = $this->toUpperAscii((string) ($row['name'] ?? ''));
                $phone = $this->toUpperAscii((string) ($row['phone'] ?? ''));
                $address = $this->toUpperAscii((string) ($row['address'] ?? ''));
                $postcode = $this->toUpperAscii((string) ($row['postcode'] ?? ''));

                if ($name === '' || $phone === '' || $address === '') {
                    continue;
                }

                if (!preg_match('/^\d{5}$/', $postcode)) {
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
        $upper = preg_replace('/[^A-Z0-9 ]+/', ' ', $upper) ?? '';
        $upper = preg_replace('/\s+/', ' ', $upper) ?? '';

        return trim($upper);
    }
    private function formatGoogleContactName(string $name): string
    {
        $formatted = Str::of($name)
            ->lower()
            ->squish()
            ->title()
            ->toString();

        if ($formatted === '') {
            return 'Sc Contact';
        }

        if (Str::startsWith(Str::lower($formatted), 'sc ')) {
            return $formatted;
        }

        return 'Sc ' . $formatted;
    }

    private function normalizeGooglePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (Str::startsWith($digits, '6')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }
    private function hasGoogleContactDuplicate(string $token, string $rawName, string $formattedName, string $normalizedPhone): bool
    {
        $targetNames = collect([
            $this->normalizeGoogleNameForCompare($rawName),
            $this->normalizeGoogleNameForCompare($formattedName),
        ])->filter()->unique()->values();

        $pageToken = null;
        $guard = 0;

        do {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get('https://people.googleapis.com/v1/people/me/connections', [
                    'personFields' => 'names,phoneNumbers',
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
                $names = collect(data_get($person, 'names', []))
                    ->pluck('displayName')
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->map(fn (string $value) => $this->normalizeGoogleNameForCompare($value))
                    ->filter();

                if ($targetNames->isNotEmpty() && $names->intersect($targetNames)->isNotEmpty()) {
                    return true;
                }

                $phones = collect(data_get($person, 'phoneNumbers', []))
                    ->pluck('value')
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->map(fn (string $value) => $this->normalizeGooglePhone($value));

                if ($normalizedPhone !== '' && $phones->contains($normalizedPhone)) {
                    return true;
                }
            }

            $pageToken = data_get($payload, 'nextPageToken');
            $guard++;
        } while (is_string($pageToken) && $pageToken !== '' && $guard < 30);

        return false;
    }

    private function normalizeGoogleNameForCompare(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9 ]+/', ' ')
            ->squish()
            ->toString();
    }

    private function generateImportEmail(string $name): string
    {
        $base = Str::slug(Str::lower($name), '.');
        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base . '@import.local';
        $counter = 1;

        while (User::query()->where('email', $candidate)->exists()) {
            $candidate = $base . '.' . $counter . '@import.local';
            $counter++;
        }

        return $candidate;
    }
}
