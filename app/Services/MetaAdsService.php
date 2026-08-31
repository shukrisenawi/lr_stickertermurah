<?php

namespace App\Services;

use App\Exceptions\MetaAdsException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class MetaAdsService
{
    private const CAMPAIGN_FIELDS = 'id,name,objective,status,effective_status,created_time,start_time,stop_time';

    private const INSIGHT_FIELDS = 'campaign_id,campaign_name,impressions,reach,clicks,spend,ctr,cpc';

    private const MAX_PAGES = 10;

    /**
     * @return array{
     *     configured: bool,
     *     appIdConfigured: bool,
     *     accessTokenConfigured: bool,
     *     adAccountConfigured: bool,
     *     appId: string|null,
     *     adAccountId: string|null,
     *     apiVersion: string,
     *     currency: string
     * }
     */
    public function configuration(): array
    {
        $appId = trim((string) config('services.meta_ads.app_id'));
        $accessToken = trim((string) config('services.meta_ads.access_token'));
        $rawAccountId = trim((string) config('services.meta_ads.ad_account_id'));
        $accountId = $this->normalizeAccountId($rawAccountId);
        $apiVersion = $this->apiVersion();
        $currency = strtoupper(trim((string) config('services.meta_ads.currency', 'MYR')));

        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            $currency = 'MYR';
        }

        return [
            'configured' => $accessToken !== '' && $accountId !== null,
            'appIdConfigured' => $appId !== '',
            'accessTokenConfigured' => $accessToken !== '',
            'adAccountConfigured' => $rawAccountId !== '' && $accountId !== null,
            'appId' => $appId !== '' ? $appId : null,
            'adAccountId' => $accountId,
            'apiVersion' => $apiVersion,
            'currency' => $currency,
        ];
    }

    /**
     * @return array{
     *     campaigns: list<array{
     *         id: string,
     *         name: string,
     *         objective: string,
     *         status: string,
     *         effectiveStatus: string,
     *         createdTime: string|null,
     *         insights: array{spend: float, impressions: int, reach: int, clicks: int, ctr: float, cpc: float}
     *     }>,
     *     summary: array{campaigns: int, activeCampaigns: int, spend: float, impressions: int, clicks: int},
     *     datePreset: string
     * }
     */
    public function dashboard(): array
    {
        $this->ensureConfigured();

        $campaigns = $this->paginate($this->accountPath().'/campaigns', [
            'fields' => self::CAMPAIGN_FIELDS,
            'limit' => 100,
        ]);
        $insights = $this->paginate($this->accountPath().'/insights', [
            'level' => 'campaign',
            'fields' => self::INSIGHT_FIELDS,
            'date_preset' => 'last_30d',
            'limit' => 100,
        ]);

        $insightsByCampaign = [];
        foreach ($insights as $insight) {
            $campaignId = $this->stringValue($insight['campaign_id'] ?? null);
            if ($campaignId === '') {
                continue;
            }

            $insightsByCampaign[$campaignId] = [
                'spend' => $this->floatValue($insight['spend'] ?? null),
                'impressions' => $this->integerValue($insight['impressions'] ?? null),
                'reach' => $this->integerValue($insight['reach'] ?? null),
                'clicks' => $this->integerValue($insight['clicks'] ?? null),
                'ctr' => $this->floatValue($insight['ctr'] ?? null),
                'cpc' => $this->floatValue($insight['cpc'] ?? null),
            ];
        }

        $normalizedCampaigns = [];
        foreach ($campaigns as $campaign) {
            $id = $this->stringValue($campaign['id'] ?? null);
            if ($id === '') {
                continue;
            }

            $status = $this->stringValue($campaign['status'] ?? null, 'PAUSED');
            $effectiveStatus = $this->stringValue($campaign['effective_status'] ?? null, $status);
            $normalizedCampaigns[] = [
                'id' => $id,
                'name' => $this->stringValue($campaign['name'] ?? null, 'Tanpa nama'),
                'objective' => $this->stringValue($campaign['objective'] ?? null, '-'),
                'status' => $status,
                'effectiveStatus' => $effectiveStatus,
                'createdTime' => $this->nullableString($campaign['created_time'] ?? null),
                'insights' => $insightsByCampaign[$id] ?? [
                    'spend' => 0.0,
                    'impressions' => 0,
                    'reach' => 0,
                    'clicks' => 0,
                    'ctr' => 0.0,
                    'cpc' => 0.0,
                ],
            ];
        }

        $summary = [
            'campaigns' => count($normalizedCampaigns),
            'activeCampaigns' => count(array_filter(
                $normalizedCampaigns,
                static fn (array $campaign): bool => $campaign['status'] === 'ACTIVE',
            )),
            'spend' => round(array_sum(array_map(
                static fn (array $campaign): float => $campaign['insights']['spend'],
                $normalizedCampaigns,
            )), 2),
            'impressions' => array_sum(array_map(
                static fn (array $campaign): int => $campaign['insights']['impressions'],
                $normalizedCampaigns,
            )),
            'clicks' => array_sum(array_map(
                static fn (array $campaign): int => $campaign['insights']['clicks'],
                $normalizedCampaigns,
            )),
        ];

        return [
            'campaigns' => $normalizedCampaigns,
            'summary' => $summary,
            'datePreset' => 'last_30d',
        ];
    }

    public function createCampaign(string $name, string $objective): string
    {
        $payload = $this->post($this->accountPath().'/campaigns', [
            'name' => trim($name),
            'objective' => $objective,
            'status' => 'PAUSED',
            'special_ad_categories' => '[]',
        ]);

        $id = $this->stringValue($payload['id'] ?? null);
        if ($id === '') {
            throw new MetaAdsException('Meta tidak memulangkan ID kempen baharu.');
        }

        return $id;
    }

    public function updateCampaign(string $campaignId, string $name, string $status): void
    {
        if (! preg_match('/^\d+$/', $campaignId)) {
            throw new MetaAdsException('ID kempen Meta tidak sah.');
        }

        $this->post($campaignId, [
            'name' => trim($name),
            'status' => $status,
        ]);
    }

    private function ensureConfigured(): void
    {
        $configuration = $this->configuration();

        if (! $configuration['accessTokenConfigured']) {
            throw new MetaAdsException('META_ACCESS_TOKEN belum ditetapkan dalam konfigurasi server.');
        }

        if (! $configuration['adAccountConfigured']) {
            throw new MetaAdsException('META_AD_ACCOUNT_ID belum ditetapkan atau tidak sah.');
        }
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    private function paginate(string $path, array $query): array
    {
        $items = [];

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $payload = $this->get($path, $query);
            $data = $payload['data'] ?? [];

            if (is_array($data)) {
                foreach ($data as $item) {
                    if (is_array($item)) {
                        $items[] = $item;
                    }
                }
            }

            $next = data_get($payload, 'paging.next');
            if (! is_string($next) || trim($next) === '') {
                break;
            }

            $path = $next;
            $query = [];
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query): array
    {
        return $this->send('get', $path, $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload): array
    {
        return $this->send('post', $path, $payload);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $data): array
    {
        $this->ensureConfigured();

        try {
            $request = Http::withToken((string) config('services.meta_ads.access_token'))
                ->acceptJson()
                ->timeout(20);

            $response = $method === 'get'
                ? $request->get($this->url($path), $data)
                : $request->asForm()->post($this->url($path), $data);
        } catch (Throwable $exception) {
            throw new MetaAdsException('Meta API tidak dapat dicapai. Sila cuba lagi.', 0, $exception);
        }

        if ($response->failed()) {
            throw new MetaAdsException($this->responseError($response));
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new MetaAdsException('Respons Meta API tidak sah.');
        }

        if (isset($payload['error'])) {
            throw new MetaAdsException($this->responseError($response));
        }

        return $payload;
    }

    private function url(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $pathHost = parse_url($path, PHP_URL_HOST);
            $pathScheme = parse_url($path, PHP_URL_SCHEME);
            $baseHost = parse_url((string) config('services.meta_ads.base_url'), PHP_URL_HOST);
            $baseScheme = parse_url((string) config('services.meta_ads.base_url'), PHP_URL_SCHEME);

            if (
                ! is_string($pathHost)
                || ! is_string($baseHost)
                || ! is_string($pathScheme)
                || ! is_string($baseScheme)
                || ! hash_equals(strtolower($baseHost), strtolower($pathHost))
                || ! hash_equals(strtolower($baseScheme), strtolower($pathScheme))
            ) {
                throw new MetaAdsException('Pautan pagination Meta API tidak sah.');
            }

            $query = parse_url($path, PHP_URL_QUERY);
            if (! is_string($query) || $query === '') {
                return $path;
            }

            parse_str($query, $queryParameters);
            unset($queryParameters['access_token']);

            $pathWithoutQuery = strtok($path, '?');
            if (! is_string($pathWithoutQuery)) {
                return $path;
            }

            return $queryParameters === []
                ? $pathWithoutQuery
                : $pathWithoutQuery.'?'.http_build_query($queryParameters);
        }

        $baseUrl = rtrim((string) config('services.meta_ads.base_url', 'https://graph.facebook.com'), '/');
        $version = $this->apiVersion();

        return $baseUrl.'/'.$version.'/'.ltrim($path, '/');
    }

    private function accountPath(): string
    {
        $accountId = $this->normalizeAccountId(trim((string) config('services.meta_ads.ad_account_id')));

        if ($accountId === null) {
            throw new MetaAdsException('META_AD_ACCOUNT_ID belum ditetapkan atau tidak sah.');
        }

        return $accountId;
    }

    private function apiVersion(): string
    {
        $version = trim((string) config('services.meta_ads.api_version', 'v25.0'), '/');

        return preg_match('/^v\d+\.\d+$/', $version) === 1 ? $version : 'v25.0';
    }

    private function normalizeAccountId(string $accountId): ?string
    {
        $accountId = preg_replace('/^act_/i', '', $accountId) ?? '';

        return preg_match('/^\d+$/', $accountId) === 1 ? 'act_'.$accountId : null;
    }

    private function responseError(Response $response): string
    {
        $message = $response->json('error.message');

        if (is_string($message) && trim($message) !== '') {
            return 'Meta API: '.trim($message);
        }

        return 'Meta API memulangkan HTTP '.$response->status().'.';
    }

    private function stringValue(mixed $value, string $fallback = ''): string
    {
        if (is_array($value)) {
            return $this->stringValue($value[0] ?? null, $fallback);
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->stringValue($value);

        return $value !== '' ? $value : null;
    }

    private function floatValue(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function integerValue(mixed $value): int
    {
        return is_numeric($value) ? (int) round((float) $value) : 0;
    }
}
