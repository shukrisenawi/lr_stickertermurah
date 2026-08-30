<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use Google\ApiCore\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class GoogleAnalyticsController extends Controller
{
    public function index(Request $request, GoogleAnalyticsService $googleAnalytics): Response
    {
        $propertyId = config('services.google_analytics.property_id');
        $credentialsPath = config('services.google_analytics.credentials');
        $credentialsConfigured = is_string($credentialsPath)
            && is_file($credentialsPath)
            && is_readable($credentialsPath);
        $report = null;
        $reportError = null;

        if (blank($propertyId)) {
            $reportError = 'GA4 Property ID belum diisi dalam konfigurasi server.';
        } elseif ($credentialsConfigured) {
            try {
                $report = $googleAnalytics->report($request->boolean('refresh'));
            } catch (Throwable $exception) {
                Log::warning('Google Analytics report failed.', [
                    'property_id' => $propertyId,
                    'message' => $exception->getMessage(),
                ]);
                $reportError = $exception instanceof ApiException && $exception->getStatus() === 'PERMISSION_DENIED'
                    ? 'Google menolak akses service account. Tambah service account sebagai Viewer pada GA4 Property.'
                    : 'Laporan GA4 tidak dapat dibaca. Semak akses service account, API dan Property ID.';
            }
        } else {
            $reportError = 'Fail credential Google tidak ditemui atau tidak boleh dibaca oleh server.';
        }

        return Inertia::render('Admin/GoogleAnalytics/Index', [
            'configuration' => [
                'measurementId' => config('services.google_analytics.measurement_id'),
                'propertyId' => $propertyId,
                'projectConfigured' => filled(config('services.google_analytics.project_id')),
                'credentialsConfigured' => $credentialsConfigured,
            ],
            'report' => $report,
            'reportError' => $reportError,
        ]);
    }
}
