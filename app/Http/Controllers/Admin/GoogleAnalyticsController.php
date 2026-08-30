<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class GoogleAnalyticsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/GoogleAnalytics/Index', [
            'configuration' => [
                'measurementId' => config('services.google_analytics.measurement_id'),
                'propertyId' => config('services.google_analytics.property_id'),
                'projectConfigured' => filled(config('services.google_analytics.project_id')),
                'credentialsConfigured' => filled(config('services.google_analytics.credentials')),
            ],
        ]);
    }
}
