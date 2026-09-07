<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\StickerDesign;
use App\Services\MalaysianStateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const SALES_PERIODS = ['weekly', 'monthly', 'yearly'];

    public function __invoke(Request $request, MalaysianStateService $malaysianStates): Response
    {
        $requestedPeriod = $request->query('period', 'monthly');
        $period = is_string($requestedPeriod) && in_array($requestedPeriod, self::SALES_PERIODS, true)
            ? $requestedPeriod
            : 'monthly';

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
            'salesStats' => $this->salesStatistics($period),
            'addressStatistics' => $addressStatistics,
        ]);
    }

    /** @return array<string, mixed> */
    private function salesStatistics(string $period): array
    {
        $today = now();
        $monthNames = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogo', 'Sep', 'Okt', 'Nov', 'Dis'];
        $periodLabels = [
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
        ];
        $periodDescriptions = [
            'weekly' => 'Jumlah nilai invoice mengikut minggu untuk 12 minggu terakhir',
            'monthly' => 'Jumlah nilai invoice mengikut bulan untuk 12 bulan terakhir',
            'yearly' => 'Jumlah nilai invoice mengikut tahun untuk 5 tahun terakhir',
        ];
        $periodRanges = [
            'weekly' => '12 minggu terakhir',
            'monthly' => '12 bulan terakhir',
            'yearly' => '5 tahun terakhir',
        ];
        $periodCount = $period === 'yearly' ? 5 : 12;
        $startDate = match ($period) {
            'weekly' => $today->copy()->startOfWeek(Carbon::MONDAY)->subWeeks($periodCount - 1),
            'yearly' => $today->copy()->startOfYear()->subYears($periodCount - 1),
            default => $today->copy()->startOfMonth()->subMonths($periodCount - 1),
        };
        $endDate = match ($period) {
            'weekly' => $today->copy()->endOfWeek(Carbon::SUNDAY),
            'yearly' => $today->copy()->endOfYear(),
            default => $today->copy()->endOfMonth(),
        };

        $invoices = Invoice::query()
            ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['issue_date', 'amount']);
        $expenses = Expense::query()
            ->whereBetween('purchase_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['purchase_date', 'amount']);

        $salesPeriods = collect(range(0, $periodCount - 1))->map(function (int $offset) use ($period, $startDate, $invoices, $expenses, $monthNames): array {
            $periodStart = match ($period) {
                'weekly' => $startDate->copy()->addWeeks($offset),
                'yearly' => $startDate->copy()->addYears($offset),
                default => $startDate->copy()->addMonths($offset),
            };
            $periodKey = match ($period) {
                'weekly' => $periodStart->format('o-W'),
                'yearly' => $periodStart->format('Y'),
                default => $periodStart->format('Y-m'),
            };
            $periodInvoices = $invoices->filter(
                fn (Invoice $invoice): bool => $invoice->issue_date?->format(match ($period) {
                    'weekly' => 'o-W',
                    'yearly' => 'Y',
                    default => 'Y-m',
                }) === $periodKey,
            );
            $periodExpenses = $expenses->filter(
                fn (Expense $expense): bool => $expense->purchase_date?->format(match ($period) {
                    'weekly' => 'o-W',
                    'yearly' => 'Y',
                    default => 'Y-m',
                }) === $periodKey,
            );
            $incomeAmount = round((float) $periodInvoices->sum(fn (Invoice $invoice): float => (float) $invoice->amount), 2);
            $expenseAmount = round((float) $periodExpenses->sum(fn (Expense $expense): float => (float) $expense->amount), 2);

            return [
                'key' => $periodKey,
                'label' => $this->salesPeriodLabel($period, $periodStart, $monthNames),
                'amount' => $incomeAmount,
                'expense_amount' => $expenseAmount,
                'profit' => round($incomeAmount - $expenseAmount, 2),
                'invoice_count' => $periodInvoices->count(),
            ];
        })->values();

        return [
            'period' => $period,
            'period_label' => $periodLabels[$period],
            'period_description' => $periodDescriptions[$period],
            'period_range' => $periodRanges[$period],
            'months' => $salesPeriods,
            'total_amount' => round((float) $salesPeriods->sum('amount'), 2),
            'total_income' => round((float) $salesPeriods->sum('amount'), 2),
            'total_expenses' => round((float) $salesPeriods->sum('expense_amount'), 2),
            'total_profit' => round((float) $salesPeriods->sum('profit'), 2),
            'total_invoices' => (int) $salesPeriods->sum('invoice_count'),
        ];
    }

    private function salesPeriodLabel(string $period, Carbon $periodStart, array $monthNames): string
    {
        if ($period === 'yearly') {
            return $periodStart->format('Y');
        }

        if ($period === 'monthly') {
            return $monthNames[(int) $periodStart->format('n') - 1].' '.$periodStart->format('y');
        }

        $periodEnd = $periodStart->copy()->endOfWeek(Carbon::SUNDAY);
        $startLabel = $periodStart->format('j').' '.$monthNames[(int) $periodStart->format('n') - 1];
        $endLabel = $periodEnd->format('j').' '.$monthNames[(int) $periodEnd->format('n') - 1];

        return $startLabel.' - '.$endLabel;
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
