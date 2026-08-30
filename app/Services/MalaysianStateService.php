<?php

namespace App\Services;

final class MalaysianStateService
{
    /** @var list<string> */
    private const STATES = [
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

    /** @return list<string> */
    public function all(): array
    {
        return self::STATES;
    }

    public function pattern(): string
    {
        return implode('|', array_map(
            static fn (string $state): string => preg_quote($state, '/'),
            self::STATES,
        ));
    }

    public function extract(?string $address): ?string
    {
        $pattern = '/(?<!\p{L})('.$this->pattern().')(?!\p{L})/iu';
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

        foreach (self::STATES as $state) {
            if (mb_strtolower($state) === $normalizedMatchedState) {
                return $state;
            }
        }

        return null;
    }
}
