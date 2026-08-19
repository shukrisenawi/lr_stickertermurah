<?php

namespace App\Services;

use App\Models\GoogleContactConnection;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use RuntimeException;

class GoogleContactsService
{
    private const PEOPLE_API_URL = 'https://people.googleapis.com/v1';

    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    /**
     * @return list<array{resource_name: string, etag: string|null, name: string, phone: string|null, email: string|null, address: string|null}>
     */
    public function listContacts(GoogleContactConnection $connection): array
    {
        $token = $this->validAccessToken($connection);
        $contacts = [];
        $pageToken = null;
        $seenPageTokens = [];

        do {
            $query = [
                'personFields' => 'names,phoneNumbers,emailAddresses,addresses,metadata',
                'pageSize' => 1000,
            ];

            if ($pageToken !== null) {
                $query['pageToken'] = $pageToken;
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->get(self::PEOPLE_API_URL.'/people/me/connections', $query)
                ->throw();

            $people = $response->json('connections', []);
            if (! is_array($people)) {
                $people = [];
            }

            foreach ($people as $person) {
                if (! is_array($person)) {
                    continue;
                }

                $resourceName = trim((string) data_get($person, 'resourceName'));
                if ($resourceName === '') {
                    continue;
                }

                $contacts[] = [
                    'resource_name' => $resourceName,
                    'etag' => $this->personEtag($person),
                    'name' => $this->personName($person),
                    'phone' => $this->firstPersonValue($person, 'phoneNumbers'),
                    'email' => $this->firstPersonValue($person, 'emailAddresses'),
                    'address' => $this->firstPersonValue($person, 'addresses', 'formattedValue'),
                ];
            }

            $nextPageToken = $response->json('nextPageToken');
            if (! is_string($nextPageToken) || $nextPageToken === '' || isset($seenPageTokens[$nextPageToken])) {
                $pageToken = null;
            } else {
                $seenPageTokens[$nextPageToken] = true;
                $pageToken = $nextPageToken;
            }
        } while ($pageToken !== null);

        usort($contacts, static fn (array $first, array $second): int => strcasecmp($first['name'], $second['name']));

        return $contacts;
    }

    /**
     * @return array{created: bool, existing_name: string|null}
     */
    public function createContact(
        GoogleContactConnection $connection,
        string $name,
        string $phone,
        ?string $email = null,
        ?string $address = null,
    ): array {
        $normalizedPhone = $this->normalizePhone($phone);

        if ($normalizedPhone === null) {
            throw new RuntimeException('Nombor telefon tidak sah.');
        }

        $token = $this->validAccessToken($connection);
        $existingContact = $this->findContactByPhone($token, $normalizedPhone);

        if ($existingContact !== null) {
            return [
                'created' => false,
                'existing_name' => $existingContact['name'],
            ];
        }

        $payload = $this->contactPayload($name, $normalizedPhone, $email, $address);

        Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->post(self::PEOPLE_API_URL.'/people:createContact?personFields=names,phoneNumbers,emailAddresses,addresses', $payload)
            ->throw();

        return [
            'created' => true,
            'existing_name' => null,
        ];
    }

    /**
     * @return array{updated: bool, existing_name: string|null}
     */
    public function updateContact(
        GoogleContactConnection $connection,
        string $resourceName,
        ?string $etag,
        string $name,
        string $phone,
        ?string $email = null,
        ?string $address = null,
    ): array {
        $normalizedPhone = $this->normalizePhone($phone);

        if ($normalizedPhone === null) {
            throw new RuntimeException('Nombor telefon tidak sah.');
        }

        $token = $this->validAccessToken($connection);
        $existingContact = $this->findContactByPhone($token, $normalizedPhone, $resourceName);

        if ($existingContact !== null) {
            return [
                'updated' => false,
                'existing_name' => $existingContact['name'],
            ];
        }

        $etag = trim((string) $etag);
        if ($etag === '') {
            $person = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->get($this->contactUrl($resourceName), ['personFields' => 'metadata'])
                ->throw()
                ->json();

            $etag = is_array($person) ? ($this->personEtag($person) ?? '') : '';
        }

        if ($etag === '') {
            throw new RuntimeException('Maklumat versi contact tidak diterima daripada Google.');
        }

        $payload = $this->contactPayload($name, $normalizedPhone, $email, $address, true);
        $payload['resourceName'] = $resourceName;
        $payload['etag'] = $etag;

        Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->patch($this->contactUrl($resourceName).':updateContact?updatePersonFields=names,phoneNumbers,emailAddresses,addresses', $payload)
            ->throw();

        return [
            'updated' => true,
            'existing_name' => null,
        ];
    }

    public function deleteContact(GoogleContactConnection $connection, string $resourceName): void
    {
        $token = $this->validAccessToken($connection);

        Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->delete($this->contactUrl($resourceName).':deleteContact')
            ->throw();
    }

    public function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', trim($phone));

        if (! is_string($digits) || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '60'.substr($digits, 1);
        }

        if (strlen($digits) < 9 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    private function validAccessToken(GoogleContactConnection $connection): string
    {
        if ($connection->expires_at === null || $connection->expires_at->isAfter(now()->addMinute())) {
            return $connection->access_token;
        }

        if (blank($connection->refresh_token)) {
            throw new RuntimeException('Sesi Google Contacts telah tamat. Sila sambung semula akaun Google.');
        }

        $provider = Socialite::driver('google');
        if (! $provider instanceof AbstractProvider) {
            throw new RuntimeException('Provider Google OAuth tidak sah.');
        }

        $token = $provider->refreshToken($connection->refresh_token);
        $connection->access_token = $token->token;

        if (filled($token->refreshToken)) {
            $connection->refresh_token = $token->refreshToken;
        }

        $connection->expires_at = filled($token->expiresIn)
            ? now()->addSeconds((int) $token->expiresIn)
            : null;
        $connection->save();

        return $connection->access_token;
    }

    /**
     * @return array{resource_name: string|null, name: string}|null
     */
    private function findContactByPhone(string $token, string $normalizedPhone, ?string $exceptResourceName = null): ?array
    {
        $pageToken = null;
        $seenPageTokens = [];

        do {
            $query = [
                'personFields' => 'names,phoneNumbers,metadata',
                'pageSize' => 1000,
            ];

            if ($pageToken !== null) {
                $query['pageToken'] = $pageToken;
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->get(self::PEOPLE_API_URL.'/people/me/connections', $query)
                ->throw();

            $people = $response->json('connections', []);
            if (! is_array($people)) {
                $people = [];
            }

            foreach ($people as $person) {
                if (! is_array($person)) {
                    continue;
                }

                $resourceName = trim((string) data_get($person, 'resourceName'));
                if ($exceptResourceName !== null && $resourceName === $exceptResourceName) {
                    continue;
                }

                $phoneNumbers = data_get($person, 'phoneNumbers', []);
                if (! is_array($phoneNumbers)) {
                    $phoneNumbers = [];
                }

                foreach ($phoneNumbers as $phoneNumber) {
                    if (! is_array($phoneNumber)) {
                        continue;
                    }

                    $candidates = [
                        data_get($phoneNumber, 'canonicalForm'),
                        data_get($phoneNumber, 'value'),
                    ];

                    foreach ($candidates as $candidate) {
                        if (is_string($candidate) && $this->normalizePhone($candidate) === $normalizedPhone) {
                            return [
                                'resource_name' => $resourceName !== '' ? $resourceName : null,
                                'name' => $this->personName($person),
                            ];
                        }
                    }
                }
            }

            $pageToken = $response->json('nextPageToken');
            if (! is_string($pageToken) || $pageToken === '' || isset($seenPageTokens[$pageToken])) {
                $pageToken = null;
            } else {
                $seenPageTokens[$pageToken] = true;
            }
        } while ($pageToken !== null);

        return null;
    }

    /**
     * @param  array<string, mixed>  $person
     */
    private function personName(array $person): string
    {
        $displayName = trim((string) data_get($person, 'names.0.displayName'));
        if ($displayName !== '') {
            return $displayName;
        }

        $unstructuredName = trim((string) data_get($person, 'names.0.unstructuredName'));
        if ($unstructuredName !== '') {
            return $unstructuredName;
        }

        return trim(implode(' ', array_filter([
            trim((string) data_get($person, 'names.0.givenName')),
            trim((string) data_get($person, 'names.0.familyName')),
        ]))) ?: 'Contact tanpa nama';
    }

    /**
     * @param  array<string, mixed>  $person
     */
    private function personEtag(array $person): ?string
    {
        $etag = trim((string) data_get($person, 'etag'));
        if ($etag !== '') {
            return $etag;
        }

        $etag = trim((string) data_get($person, 'metadata.sources.0.etag'));

        return $etag !== '' ? $etag : null;
    }

    /**
     * @param  array<string, mixed>  $person
     */
    private function firstPersonValue(array $person, string $field, string $valueKey = 'value'): ?string
    {
        $value = trim((string) data_get($person, $field.'.0.'.$valueKey));

        return $value !== '' ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function contactPayload(
        string $name,
        string $normalizedPhone,
        ?string $email,
        ?string $address,
        bool $includeEmptyFields = false,
    ): array {
        $payload = [
            'names' => [[
                'unstructuredName' => trim($name),
            ]],
            'phoneNumbers' => [[
                'value' => '+'.$normalizedPhone,
                'type' => 'mobile',
            ]],
        ];

        $email = trim((string) $email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && ! str_ends_with(mb_strtolower($email), '@import.local')) {
            $payload['emailAddresses'] = [[
                'value' => $email,
                'type' => 'home',
            ]];
        } elseif ($includeEmptyFields) {
            $payload['emailAddresses'] = [];
        }

        $address = trim((string) $address);
        if ($address !== '') {
            $payload['addresses'] = [[
                'formattedValue' => $address,
                'type' => 'home',
            ]];
        } elseif ($includeEmptyFields) {
            $payload['addresses'] = [];
        }

        return $payload;
    }

    private function contactUrl(string $resourceName): string
    {
        if (! preg_match('/^people\/[^\/]+$/', $resourceName)) {
            throw new RuntimeException('Resource name Google Contact tidak sah.');
        }

        return self::PEOPLE_API_URL.'/'.$resourceName;
    }
}
