<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use App\Models\Order;
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
        $this->trackUserActivity($request);

        $customerAddresses = [];
        $whatsappPhone = '01169409606';
        $adminEmail = 'stickertermurah@gmail.com';
        $invoiceCounts = [
            'adminPending' => 0,
            'memberUnpaid' => 0,
        ];
        $orderCounts = [
            'adminPending' => 0,
        ];
        $adminNotifications = [];
        $memberNotifications = [];
        $memberNotificationUnreadCount = 0;
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

            if (! $request->user()->is_admin) {
                try {
                    $memberNotificationUnreadCount = $request->user()->unreadNotifications()->count();
                    $memberNotifications = $request->user()->notifications()
                        ->latest()
                        ->limit(10)
                        ->get()
                        ->map(fn ($notification): array => [
                            'id' => (string) $notification->id,
                            'title' => (string) data_get($notification->data, 'title', 'Kemas kini admin'),
                            'message' => (string) data_get($notification->data, 'message', ''),
                            'type' => (string) data_get($notification->data, 'type', 'admin_update'),
                            'url' => (string) (data_get($notification->data, 'url') ?: route('member.dashboard')),
                            'read_at' => $notification->read_at?->toIso8601String(),
                            'created_at' => $notification->created_at?->toIso8601String(),
                        ])
                        ->all();
                } catch (\Throwable) {
                    // Gunakan nilai kosong jika jadual notifikasi belum tersedia semasa proses deploy.
                }

                $orderCounts['memberAwaitingApproval'] = Order::query()
                    ->where('user_id', $request->user()->id)
                    ->where('pricing_status', 'awaiting_customer_approval')
                    ->count();
            }

            if ($request->user()->is_admin) {
                $orderCounts['adminPending'] = Order::query()
                    ->where('status', 'pending')
                    ->count();

                $invoiceCounts['adminPending'] = Invoice::query()
                    ->whereIn('payment_status', ['unpaid', 'submitted', 'rejected'])
                    ->count();

                $testimonialCounts['adminPending'] = Testimonial::query()
                    ->where('is_approved', false)
                    ->count();

                if ($orderCounts['adminPending'] > 0) {
                    $adminNotifications[] = [
                        'key' => 'orders-pending',
                        'label' => 'Order menunggu semakan',
                        'count' => $orderCounts['adminPending'],
                        'href' => route('admin.orders.index', ['status' => 'pending']),
                    ];
                }

                if ($invoiceCounts['adminPending'] > 0) {
                    $adminNotifications[] = [
                        'key' => 'invoices-pending',
                        'label' => 'Invoice perlu tindakan',
                        'count' => $invoiceCounts['adminPending'],
                        'href' => route('admin.invoices.index'),
                    ];
                }

                if ($testimonialCounts['adminPending'] > 0) {
                    $adminNotifications[] = [
                        'key' => 'testimonials-pending',
                        'label' => 'Testimoni menunggu semakan',
                        'count' => $testimonialCounts['adminPending'],
                        'href' => route('admin.testimonials.index'),
                    ];
                }
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
            'orderCounts' => $orderCounts,
            'adminNotifications' => $adminNotifications,
            'memberNotifications' => $memberNotifications,
            'memberNotificationUnreadCount' => $memberNotificationUnreadCount,
            'testimonialCounts' => $testimonialCounts,
        ];
    }

    private function trackUserActivity(Request $request): void
    {
        if ($request->session()->has('impersonate_admin_id')) {
            return;
        }

        $user = $request->user();
        if ($user) {
            $user->markLastSeen();
        }
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
