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
        if (Setting::getValue('under_construction', '0') === '1') {
            if (! Auth::check() || ! Auth::user()?->is_admin) {
                return Inertia::render('Public/UnderConstruction')
                    ->toResponse($request);
            }
        }

        return $next($request);
    }
}
