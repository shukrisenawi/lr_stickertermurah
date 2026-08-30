<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\StickerDesign;
use App\Services\MalaysianStateService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(MalaysianStateService $malaysianStates): Response
    {
        $startDate = now()->startOfMonth()->subMonths(11);
        $endDate = now()->endOfMonth();
        $monthNames = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogo', 'Sep', 'Okt', 'Nov', 'Dis'];

        $monthlyInvoices = Invoice::query()
            ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['issue_date', 'amount']);

        $salesMonths = collect(range(0, 11))->map(function (int $offset) use ($startDate, $monthlyInvoices, $monthNames): array {
            $month = $startDate->copy()->addMonths($offset);
            $monthKey = $month->format('Y-m');
            $monthInvoices = $monthlyInvoices->filter(
                fn (Invoice $invoice): bool => $invoice->issue_date?->format('Y-m') === $monthKey,
            );

            return [
                'key' => $monthKey,
                'label' => $monthNames[(int) $month->format('n') - 1].' '.$month->format('y'),
                'amount' => round((float) $monthInvoices->sum(fn (Invoice $invoice): float => (float) $invoice->amount), 2),
                'invoice_count' => $monthInvoices->count(),
            ];
        })->values();

        $recentInvoices = Invoice::query()
            ->with(['user', 'order'])
            ->latest('issue_date')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Invoice $invoice): array => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer_name' => $invoice->customer_name ?? $invoice->order?->customer_name ?? $invoice->user?->name ?? '-',
                'amount' => (float) $invoice->amount,
                'payment_status' => $invoice->payment_status,
                'issue_date' => $invoice->issue_date?->format('Y-m-d'),
            ])->values();

        $addressStatistics = $this->defaultAddressStatistics($malaysianStates);

        return Inertia::render('Admin/Dashboard', [
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->whereIn('status', ['pending', 'paid', 'processing'])->count(),
            'totalDesigns' => StickerDesign::query()->count(),
            'totalCategories' => Category::query()->count(),
            'recentInvoices' => $recentInvoices,
            'salesStats' => [
                'months' => $salesMonths,
                'total_amount' => round((float) $salesMonths->sum('amount'), 2),
                'total_invoices' => (int) $salesMonths->sum('invoice_count'),
            ],
            'addressStatistics' => $addressStatistics,
        ]);
    }

    /** @return array<string, mixed> */
    private function defaultAddressStatistics(MalaysianStateService $malaysianStates): array
    {
        $stateCounts = array_fill_keys($malaysianStates->all(), 0);
        $totalDefaultAddresses = 0;
        $classifiedAddresses = 0;

        CustomerAddress::query()
            ->where('is_default', true)
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->get(['address'])
            ->each(function (CustomerAddress $address) use ($malaysianStates, &$stateCounts, &$totalDefaultAddresses, &$classifiedAddresses): void {
                $totalDefaultAddresses++;
                $state = $malaysianStates->extract($address->address);

                if ($state === null) {
                    return;
                }

                $stateCounts[$state]++;
                $classifiedAddresses++;
            });

        return [
            'states' => collect($stateCounts)
                ->filter(fn (int $count): bool => $count > 0)
                ->map(fn (int $count, string $state): array => [
                    'state' => $state,
                    'count' => $count,
                ])
                ->sortByDesc('count')
                ->values()
                ->all(),
            'total_default_addresses' => $totalDefaultAddresses,
            'classified_addresses' => $classifiedAddresses,
            'unclassified_addresses' => $totalDefaultAddresses - $classifiedAddresses,
        ];
    }
}
