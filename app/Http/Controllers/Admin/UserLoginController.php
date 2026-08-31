<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserLoginController extends Controller
{
    public function index(): Response
    {
        $onlineSince = now()->subMinutes(15);

        $users = User::query()
            ->select(['id', 'name', 'email', 'no_tel', 'is_admin', 'last_login_at', 'last_seen_at'])
            ->where('is_admin', false)
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->orderByDesc('last_seen_at')
            ->orderBy('name')
            ->paginate(20)
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'no_tel' => $user->no_tel,
                'is_admin' => $user->is_admin,
                'last_login_at' => $user->last_login_at?->toIso8601String(),
                'last_seen_at' => $user->last_seen_at?->toIso8601String(),
                'is_online' => $user->last_seen_at?->greaterThanOrEqualTo($onlineSince) ?? false,
            ]);

        return Inertia::render('Admin/UserLogin/Index', [
            'users' => $users,
            'summary' => [
                'total' => User::query()->where('is_admin', false)->count(),
                'loggedIn' => User::query()->where('is_admin', false)->whereNotNull('last_login_at')->count(),
                'online' => User::query()->where('is_admin', false)->where('last_seen_at', '>=', $onlineSince)->count(),
            ],
        ]);
    }
}
