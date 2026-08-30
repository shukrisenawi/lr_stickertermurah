<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunRealtimeReportResponse;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use JsonException;
use RuntimeException;

class GoogleAnalyticsService
{
    private const READONLY_SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';

    /**
     * @return array{
     *     generatedAt: string,
     *     dateRange: array{start: string, end: string},
     *     summary: array{activeUsers: int, newUsers: int, sessions: int, engagedSessions: int, eventCount: int, screenPageViews: int},
     *     trend: list<array{date: string, activeUsers: int, sessions: int, screenPageViews: int}>,
     *     topPages: list<array{title: string, path: string, screenPageViews: int, activeUsers: int}>,
     *     topSources: list<array{channel: string, sessions: int, activeUsers: int}>,
     *     regions: list<array{name: string, activeUsers: int}>,
     *     ageBrackets: list<array{bracket: string, activeUsers: int}>,
     *     realtimeActiveUsers: int
     * }
     */
    public function report(bool $forceRefresh = false): array
    {
        $propertyId = trim((string) config('services.google_analytics.property_id'));
        $credentialsPath = trim((string) config('services.google_analytics.credentials'));

        if ($propertyId === '') {
            throw new RuntimeException('GA4 Property ID belum diisi.');
        }

        if ($credentialsPath === '' || ! is_file($credentialsPath) || ! is_readable($credentialsPath)) {
            throw new RuntimeException('Fail credential Google tidak ditemui pada server.');
        }

        $today = now();
        $startDate = $today->copy()->subDays(29)->toDateString();
        $endDate = $today->toDateString();
        $cacheKey = "google-analytics.report.{$propertyId}.{$startDate}.{$endDate}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            fn (): array => $this->fetchReport($propertyId, $credentialsPath, $startDate, $endDate),
        );
    }

    /**
     * @return array{
     *     generatedAt: string,
     *     dateRange: array{start: string, end: string},
     *     summary: array{activeUsers: int, newUsers: int, sessions: int, engagedSessions: int, eventCount: int, screenPageViews: int},
     *     trend: list<array{date: string, activeUsers: int, sessions: int, screenPageViews: int}>,
     *     topPages: list<array{title: string, path: string, screenPageViews: int, activeUsers: int}>,
     *     topSources: list<array{channel: string, sessions: int, activeUsers: int}>,
     *     regions: list<array{name: string, activeUsers: int}>,
     *     ageBrackets: list<array{bracket: string, activeUsers: int}>,
     *     realtimeActiveUsers: int
     * }
     */
    private function fetchReport(string $propertyId, string $credentialsPath, string $startDate, string $endDate): array
    {
        $client = new BetaAnalyticsDataClient([
            'credentials' => new ServiceAccountCredentials(
                [self::READONLY_SCOPE],
                $this->loadCredentials($credentialsPath),
            ),
            'transport' => 'rest',
        ]);

        try {
            $summaryRows = $this->responseRows($client->runReport($this->reportRequest(
                $propertyId,
                $startDate,
                $endDate,
                [],
                ['activeUsers', 'newUsers', 'sessions', 'engagedSessions', 'eventCount', 'screenPageViews'],
                limit: 1,
            )));

            $trendRows = $this->responseRows($client->runReport($this->reportRequest(
                $propertyId,
                $startDate,
                $endDate,
                ['date'],
                ['activeUsers', 'sessions', 'screenPageViews'],
                orderBy: $this->dimensionOrder('date'),
                limit: 31,
            )));

            $pageRows = $this->responseRows($client->runReport($this->reportRequest(
                $propertyId,
                $startDate,
                $endDate,
                ['pageTitle', 'pagePath'],
                ['screenPageViews', 'activeUsers'],
                orderBy: $this->metricOrder('screenPageViews'),
                limit: 8,
            )));

            $sourceRows = $this->responseRows($client->runReport($this->reportRequest(
                $propertyId,
                $startDate,
                $endDate,
                ['sessionDefaultChannelGroup'],
                ['sessions', 'activeUsers'],
                orderBy: $this->metricOrder('sessions'),
                limit: 8,
            )));

            $regionRows = $this->responseRows($client->runReport($this->reportRequest(
                $propertyId,
                $startDate,
                $endDate,
                ['region'],
                ['activeUsers'],
                orderBy: $this->metricOrder('activeUsers'),
                dimensionFilter: $this->exactDimensionFilter('country', 'Malaysia'),
                limit: 10,
            )));

            $ageRows = $this->responseRows($client->runReport($this->reportRequest(
                $propertyId,
                $startDate,
                $endDate,
                ['userAgeBracket'],
                ['activeUsers'],
                orderBy: $this->metricOrder('activeUsers'),
                limit: 10,
            )));

            $realtimeRows = $this->responseRows($client->runRealtimeReport(
                (new RunRealtimeReportRequest)
                    ->setProperty("properties/{$propertyId}")
                    ->setMetrics($this->metrics(['activeUsers'])),
            ));
        } finally {
            $client->close();
        }

        $summary = $summaryRows[0] ?? ['dimensions' => [], 'metrics' => []];

        return [
            'generatedAt' => now()->toIso8601String(),
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'activeUsers' => $this->metricValue($summary, 0),
                'newUsers' => $this->metricValue($summary, 1),
                'sessions' => $this->metricValue($summary, 2),
                'engagedSessions' => $this->metricValue($summary, 3),
                'eventCount' => $this->metricValue($summary, 4),
                'screenPageViews' => $this->metricValue($summary, 5),
            ],
            'trend' => array_map(fn (array $row): array => [
                'date' => $this->dateValue($row, 0),
                'activeUsers' => $this->metricValue($row, 0),
                'sessions' => $this->metricValue($row, 1),
                'screenPageViews' => $this->metricValue($row, 2),
            ], $trendRows),
            'topPages' => array_map(fn (array $row): array => [
                'title' => $this->dimensionValue($row, 0, '(not set)'),
                'path' => $this->dimensionValue($row, 1, '(not set)'),
                'screenPageViews' => $this->metricValue($row, 0),
                'activeUsers' => $this->metricValue($row, 1),
            ], $pageRows),
            'topSources' => array_map(fn (array $row): array => [
                'channel' => $this->dimensionValue($row, 0, '(not set)'),
                'sessions' => $this->metricValue($row, 0),
                'activeUsers' => $this->metricValue($row, 1),
            ], $sourceRows),
            'regions' => array_map(fn (array $row): array => [
                'name' => $this->dimensionValue($row, 0, '(not set)'),
                'activeUsers' => $this->metricValue($row, 0),
            ], $regionRows),
            'ageBrackets' => array_map(fn (array $row): array => [
                'bracket' => $this->dimensionValue($row, 0, '(not set)'),
                'activeUsers' => $this->metricValue($row, 0),
            ], $ageRows),
            'realtimeActiveUsers' => $this->metricValue($realtimeRows[0] ?? ['dimensions' => [], 'metrics' => []], 0),
        ];
    }

    /**
     * @param  list<string>  $dimensionNames
     * @param  list<string>  $metricNames
     */
    private function reportRequest(
        string $propertyId,
        string $startDate,
        string $endDate,
        array $dimensionNames,
        array $metricNames,
        ?OrderBy $orderBy = null,
        ?FilterExpression $dimensionFilter = null,
        int $limit = 10,
    ): RunReportRequest {
        $request = (new RunReportRequest)
            ->setProperty("properties/{$propertyId}")
            ->setMetrics($this->metrics($metricNames))
            ->setDateRanges([
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ])
            ->setLimit($limit);

        if ($dimensionNames !== []) {
            $request->setDimensions($this->dimensions($dimensionNames));
        }

        if ($orderBy !== null) {
            $request->setOrderBys([$orderBy]);
        }

        if ($dimensionFilter !== null) {
            $request->setDimensionFilter($dimensionFilter);
        }

        return $request;
    }

    /**
     * @param  list<string>  $names
     * @return list<Dimension>
     */
    private function dimensions(array $names): array
    {
        return array_map(
            static fn (string $name): Dimension => new Dimension(['name' => $name]),
            $names,
        );
    }

    private function exactDimensionFilter(string $dimensionName, string $value): FilterExpression
    {
        return (new FilterExpression)->setFilter(
            (new Filter)
                ->setFieldName($dimensionName)
                ->setStringFilter(
                    (new StringFilter)
                        ->setMatchType(MatchType::EXACT)
                        ->setValue($value),
                ),
        );
    }

    /**
     * @param  list<string>  $names
     * @return list<Metric>
     */
    private function metrics(array $names): array
    {
        return array_map(
            static fn (string $name): Metric => new Metric(['name' => $name]),
            $names,
        );
    }

    private function metricOrder(string $metricName): OrderBy
    {
        return (new OrderBy)
            ->setMetric(new MetricOrderBy(['metric_name' => $metricName]))
            ->setDesc(true);
    }

    private function dimensionOrder(string $dimensionName): OrderBy
    {
        return (new OrderBy)
            ->setDimension(new DimensionOrderBy(['dimension_name' => $dimensionName]))
            ->setDesc(false);
    }

    /**
     * @return list<array{dimensions: list<string>, metrics: list<string>}>
     */
    private function responseRows(RunReportResponse|RunRealtimeReportResponse $response): array
    {
        $rows = [];

        foreach ($response->getRows() as $row) {
            $dimensions = [];
            foreach ($row->getDimensionValues() as $value) {
                $dimensions[] = $value->getValue();
            }

            $metrics = [];
            foreach ($row->getMetricValues() as $value) {
                $metrics[] = $value->getValue();
            }

            $rows[] = [
                'dimensions' => $dimensions,
                'metrics' => $metrics,
            ];
        }

        return $rows;
    }

    /**
     * @param  array{dimensions: list<string>, metrics: list<string>}  $row
     */
    private function metricValue(array $row, int $index): int
    {
        $value = $row['metrics'][$index] ?? '0';

        return is_numeric($value) ? (int) round((float) $value) : 0;
    }

    /**
     * @param  array{dimensions: list<string>, metrics: list<string>}  $row
     */
    private function dimensionValue(array $row, int $index, string $fallback): string
    {
        $value = trim((string) ($row['dimensions'][$index] ?? ''));

        return $value !== '' ? $value : $fallback;
    }

    /**
     * @param  array{dimensions: list<string>, metrics: list<string>}  $row
     */
    private function dateValue(array $row, int $index): string
    {
        $value = $this->dimensionValue($row, $index, '');
        $date = \DateTimeImmutable::createFromFormat('!Ymd', $value);

        return $date instanceof \DateTimeImmutable ? $date->format('Y-m-d') : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadCredentials(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Fail credential Google tidak dapat dibaca oleh server.');
        }

        try {
            $credentials = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('Fail credential Google bukan JSON yang sah.');
        }

        if (! is_array($credentials)
            || ($credentials['type'] ?? null) !== 'service_account'
            || ! is_string($credentials['client_email'] ?? null)
            || ! is_string($credentials['private_key'] ?? null)
        ) {
            throw new RuntimeException('Fail credential Google mesti menggunakan service account JSON.');
        }

        return $credentials;
    }
}
