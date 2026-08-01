<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MemberMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('member.login');
        }

        // Admin tidak boleh mengakses laman ahli; redirect ke laman admin
        if (Auth::user()?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
