<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class UnderConstructionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('home') && ($request->header('Sec-CH-UA-Mobile') === '?1'
            || preg_match('/Android|iPhone|iPad|iPod|Mobile|IEMobile|Opera Mini/i', $request->userAgent() ?? ''))) {
            return redirect()->route('sticker.price-list')->withHeaders([
                'Vary' => 'User-Agent, Sec-CH-UA-Mobile',
                'Cache-Control' => 'private, no-store',
            ]);
        }

        $hasAdminAccess = Auth::check() && (
            Auth::user()?->is_admin
            || $request->session()->has('impersonate_admin_id')
        );

        if (Setting::getValue('under_construction', '0') === '1' && ! $hasAdminAccess) {
            return Inertia::render('Public/UnderConstruction')
                ->toResponse($request);
        }

        return $next($request);
    }
}
