<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use App\Models\PaymentSetting;
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
        $whatsappPhone = '01169409606';
        $invoiceCounts = [
            'adminPending' => 0,
            'memberUnpaid' => 0,
        ];

        if ($request->user()) {
            try {
                $customerAddresses = $request->user()->customerAddresses()->get()->map(fn ($addr) => [
                    'id' => $addr->id,
                    'recipient_name' => $addr->recipient_name ?: $request->user()->name,
                    'address' => $addr->address,
                    'no_hp' => $addr->no_hp,
                    'is_default' => $addr->is_default,
                ]);
            } catch (\Throwable $e) {
                $customerAddresses = [];
            }

            if ($request->user()->is_admin) {
                $invoiceCounts['adminPending'] = Invoice::query()
                    ->whereIn('payment_status', ['unpaid', 'submitted', 'rejected'])
                    ->count();
            }

            $invoiceCounts['memberUnpaid'] = Invoice::query()
                ->where('payment_status', '!=', 'paid')
                ->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id)
                        ->orWhereHas('order', function ($orderQuery) use ($request) {
                            $orderQuery->where('user_id', $request->user()->id);
                        });
                })
                ->count();
        }

        try {
            $whatsappPhone = preg_replace('/\D+/', '', PaymentSetting::query()->value('admin_phone') ?? $whatsappPhone) ?: $whatsappPhone;
        } catch (\Throwable) {
            // Gunakan nombor fallback sebelum jadual setting tersedia, contohnya semasa test migration.
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'no_tel' => $request->user()->no_tel,
                    'email' => $request->user()->email,
                    'is_admin' => $request->user()->is_admin,
                    'avatar_url' => $request->user()->avatar_path
                        ? asset('storage/'.$request->user()->avatar_path)
                        : null,
                ] : null,
                'customerAddresses' => $customerAddresses,
                'impersonating' => (bool) $request->session()->get('impersonate_admin_id'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'logo_url' => asset('images/logo-baru.png'),
                'whatsapp_phone' => $whatsappPhone,
            ],
            'invoiceCounts' => $invoiceCounts,
        ];
    }
}
