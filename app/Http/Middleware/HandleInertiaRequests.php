<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $customerAddresses = [];

        if ($request->user()) {
            try {
                $customerAddresses = $request->user()->customerAddresses()->get()->map(fn ($addr) => [
                    'id' => $addr->id,
                    'label' => $addr->label,
                    'address' => $addr->address,
                    'phone' => $addr->phone,
                    'is_default' => $addr->is_default,
                ]);
            } catch (\Throwable $e) {
                $customerAddresses = [];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'is_admin' => $request->user()->is_admin,
                    'avatar_url' => $request->user()->avatar_path
                        ? asset('storage/' . $request->user()->avatar_path)
                        : null,
                ] : null,
                'customerAddresses' => $customerAddresses,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'logo_url' => asset('images/logo-baru.png'),
            ],
        ];
    }
}
