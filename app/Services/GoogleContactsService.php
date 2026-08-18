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
        $existingName = $this->findContactNameByPhone($token, $normalizedPhone);

        if ($existingName !== null) {
            return [
                'created' => false,
                'existing_name' => $existingName,
            ];
        }

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
        }

        $address = trim((string) $address);
        if ($address !== '') {
            $payload['addresses'] = [[
                'formattedValue' => $address,
                'type' => 'home',
            ]];
        }

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

    private function findContactNameByPhone(string $token, string $normalizedPhone): ?string
    {
        $pageToken = null;
        $seenPageTokens = [];

        do {
            $query = [
                'personFields' => 'names,phoneNumbers',
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

            foreach ($response->json('connections', []) as $person) {
                foreach (data_get($person, 'phoneNumbers', []) as $phoneNumber) {
                    $candidates = [
                        data_get($phoneNumber, 'canonicalForm'),
                        data_get($phoneNumber, 'value'),
                    ];

                    foreach ($candidates as $candidate) {
                        if (is_string($candidate) && $this->normalizePhone($candidate) === $normalizedPhone) {
                            return trim((string) data_get($person, 'names.0.displayName')) ?: 'Contact tanpa nama';
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
}
