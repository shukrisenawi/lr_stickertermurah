<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\StickerDesign;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
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
        ]);
    }
}
