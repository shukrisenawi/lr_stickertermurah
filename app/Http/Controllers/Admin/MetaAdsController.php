<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\MetaAdsException;
use App\Http\Controllers\Controller;
use App\Services\MetaAdsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class MetaAdsController extends Controller
{
    private const OBJECTIVES = [
        'OUTCOME_AWARENESS',
        'OUTCOME_TRAFFIC',
        'OUTCOME_ENGAGEMENT',
        'OUTCOME_LEADS',
        'OUTCOME_APP_PROMOTION',
        'OUTCOME_SALES',
    ];

    public function index(MetaAdsService $metaAds): Response
    {
        $configuration = $metaAds->configuration();
        $dashboard = [
            'campaigns' => [],
            'summary' => [
                'campaigns' => 0,
                'activeCampaigns' => 0,
                'spend' => 0,
                'impressions' => 0,
                'clicks' => 0,
            ],
            'datePreset' => 'last_30d',
        ];
        $reportError = null;

        if ($configuration['configured']) {
            try {
                $dashboard = $metaAds->dashboard();
            } catch (MetaAdsException $exception) {
                Log::warning('Meta Ads dashboard failed.', [
                    'ad_account_id' => $configuration['adAccountId'],
                    'message' => $exception->getMessage(),
                ]);
                $reportError = $exception->getMessage();
            } catch (Throwable $exception) {
                Log::warning('Meta Ads dashboard failed unexpectedly.', [
                    'ad_account_id' => $configuration['adAccountId'],
                    'message' => $exception->getMessage(),
                ]);
                $reportError = 'Laporan Meta Ads tidak dapat dibaca. Sila cuba lagi.';
            }
        } elseif (! $configuration['accessTokenConfigured']) {
            $reportError = 'Tetapkan META_ACCESS_TOKEN dalam .env untuk menyambung Meta Ads.';
        } elseif (! $configuration['adAccountConfigured']) {
            $reportError = 'Tetapkan META_AD_ACCOUNT_ID yang sah dalam .env untuk menyambung Meta Ads.';
        }

        return Inertia::render('Admin/MetaAds/Index', [
            'configuration' => $configuration,
            'campaigns' => $dashboard['campaigns'],
            'summary' => $dashboard['summary'],
            'datePreset' => $dashboard['datePreset'],
            'reportError' => $reportError,
        ]);
    }

    public function store(Request $request, MetaAdsService $metaAds): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string', 'in:'.implode(',', self::OBJECTIVES)],
        ]);

        try {
            $metaAds->createCampaign($validated['name'], $validated['objective']);
        } catch (MetaAdsException $exception) {
            Log::warning('Meta Ads campaign creation failed.', ['message' => $exception->getMessage()]);

            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.meta-ads.index')->with('success', 'Kempen Meta berjaya dicipta sebagai PAUSED.');
    }

    public function update(string $campaignId, Request $request, MetaAdsService $metaAds): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:ACTIVE,PAUSED'],
        ]);

        try {
            $metaAds->updateCampaign($campaignId, $validated['name'], $validated['status']);
        } catch (MetaAdsException $exception) {
            Log::warning('Meta Ads campaign update failed.', [
                'campaign_id' => $campaignId,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', $exception->getMessage());
        }

        $message = $validated['status'] === 'ACTIVE'
            ? 'Kempen Meta diaktifkan. Semak bajet dan sasaran di Ads Manager.'
            : 'Kempen Meta dijeda.';

        return redirect()->route('admin.meta-ads.index')->with('success', $message);
    }
}
