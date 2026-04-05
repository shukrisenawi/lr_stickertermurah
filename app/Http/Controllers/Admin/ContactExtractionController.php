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
        $rawText = (string) $request->session()->get('contact_extract.raw_text', '');

        return view('admin.contacts.extract', [
            'rawText' => $rawText,
            'contacts' => $this->buildContactsWithSuggestions($rawText),
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

    public function addUser(Request $request): View
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'postcode' => ['nullable', 'string'],
        ]);

        $name = $validated['name'];
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

        $rawText = (string) $request->session()->get('contact_extract.raw_text', '');

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
                $results[] = [
                    'id' => (int) $user->id,
                    'name' => $this->toUpperAscii($user->name),
                    'email' => $user->email,
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





