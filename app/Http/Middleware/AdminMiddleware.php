<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika admin sedang impersonate user, auto-kembali ke akaun admin
        if (Auth::check() && ! Auth::user()?->is_admin) {
            $adminId = $request->session()->get('impersonate_admin_id');

            if ($adminId && ($admin = User::query()->find($adminId)) && $admin->is_admin) {
                Auth::login($admin, false);
                $request->session()->forget('impersonate_admin_id');
                $request->session()->regenerate();
            }
        }

        if (! Auth::check() || ! Auth::user()?->is_admin) {
            return redirect()->route('admin.login')->with('error', 'Akses admin sahaja.');
        }

        return $next($request);
    }
}