<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\SeoMetadata;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $this->restoreAdminForAdminRequest($request);

        $customerAddresses = [];
        $whatsappPhone = '01169409606';
        $adminEmail = 'stickertermurah@gmail.com';
        $invoiceCounts = [
            'adminPending' => 0,
            'memberUnpaid' => 0,
        ];
        $testimonialCounts = [
            'adminPending' => 0,
            'approved' => 0,
        ];

        try {
            $testimonialCounts['approved'] = Testimonial::query()
                ->where('is_approved', true)
                ->count();
        } catch (\Throwable) {
            // Gunakan nilai fallback sebelum jadual testimoni tersedia.
        }

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

                $testimonialCounts['adminPending'] = Testimonial::query()
                    ->where('is_approved', false)
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
            $paymentSettings = PaymentSetting::query()->first(['admin_phone', 'admin_email']);
            $whatsappPhone = preg_replace('/\D+/', '', $paymentSettings?->admin_phone ?? $whatsappPhone) ?: $whatsappPhone;
            $adminEmail = $paymentSettings?->admin_email ?: $adminEmail;
        } catch (\Throwable) {
            // Gunakan fallback sebelum jadual setting tersedia, contohnya semasa test migration.
        }

        $seo = app(SeoMetadata::class)->for($request, $whatsappPhone, $adminEmail);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'no_tel' => $request->user()->no_tel,
                    'email' => $request->user()->email,
                    'must_change_password' => $request->user()->must_change_password,
                    'is_admin' => $request->user()->is_admin,
                    'avatar_url' => $request->user()->avatar_path
                        ? asset('storage/'.$request->user()->avatar_path)
                        : null,
                ] : null,
                'customerAddresses' => $customerAddresses,
                'impersonating' => $request->user()?->is_admin === false
                    && $request->session()->has('impersonate_admin_id'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'logo_url' => asset('images/logo-baru.webp'),
                'whatsapp_phone' => $whatsappPhone,
                'admin_email' => $adminEmail,
            ],
            'seo' => $seo,
            'invoiceCounts' => $invoiceCounts,
            'testimonialCounts' => $testimonialCounts,
        ];
    }

    private function restoreAdminForAdminRequest(Request $request): void
    {
        if (! $request->is('admin', 'admin/*') || $request->is('admin/return')) {
            return;
        }

        $user = Auth::user();
        $adminId = $request->session()->get('impersonate_admin_id');

        if (! $user || $user->is_admin || ! $adminId) {
            return;
        }

        $admin = User::query()->find($adminId);

        if (! $admin?->is_admin) {
            return;
        }

        /** @var SessionGuard $guard */
        $guard = Auth::guard();
        $guard->setUser($admin);
        $request->session()->put($guard->getName(), $admin->getAuthIdentifier());
        $request->session()->forget('impersonate_admin_id');
    }
}
