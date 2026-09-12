<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankMonthlyRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    private const MIN_YEAR = 2000;

    private const MAX_YEAR = 2100;

    public function index(Request $request): Response
    {
        $currentYear = now()->year;
        $year = $this->requestedYear($request, $currentYear);
        $bankAccounts = BankAccount::query()
            ->withCount('monthlyRecords')
            ->with(['monthlyRecords' => fn ($query) => $query
                ->where('year', $year)
                ->orderBy('month')])
            ->orderBy('name')
            ->get();

        $banks = $bankAccounts->map(fn (BankAccount $bankAccount): array => $this->serializeBank($bankAccount))->values();

        return Inertia::render('Admin/BankAccounts/Index', [
            'banks' => $banks,
            'year' => $year,
            'currentYear' => $currentYear,
            'currentMonth' => now()->month,
            'years' => BankMonthlyRecord::query()
                ->select('year')
                ->distinct()
                ->pluck('year')
                ->map(fn ($storedYear): int => (int) $storedYear)
                ->push($currentYear)
                ->unique()
                ->sortDesc()
                ->values(),
            'totals' => [
                'current_balance' => round((float) $banks->sum('current_balance'), 2),
                'income' => round((float) $banks->sum('income'), 2),
                'expense' => round((float) $banks->sum('expense'), 2),
                'bank_count' => $banks->count(),
            ],
        ]);
    }

    public function storeBank(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->bankRules());

        BankAccount::query()->create($validated);

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Akaun bank berjaya ditambah.');
    }

    public function updateBank(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $validated = $request->validate($this->bankRules());

        $bankAccount->update($validated);

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Akaun bank berjaya dikemaskini.');
    }

    public function destroyBank(BankAccount $bankAccount): RedirectResponse
    {
        if ($bankAccount->monthlyRecords()->exists()) {
            return back()->with('error', 'Akaun bank yang mempunyai rekod bulanan tidak boleh dipadam.');
        }

        $bankAccount->delete();

        return back()->with('success', 'Akaun bank berjaya dipadam.');
    }

    public function storeMonthlyRecord(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->monthlyRecordRules());

        BankMonthlyRecord::query()->updateOrCreate(
            [
                'bank_account_id' => $validated['bank_account_id'],
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'opening_balance' => $validated['opening_balance'],
                'income' => $validated['income'],
                'expense' => $validated['expense'],
                'notes' => $validated['notes'] ?? null,
            ],
        );

        return redirect()
            ->route('admin.bank-accounts.index', ['year' => $validated['year']])
            ->with('success', 'Rekod bulanan berjaya disimpan.');
    }

    public function updateMonthlyRecord(Request $request, BankMonthlyRecord $bankMonthlyRecord): RedirectResponse
    {
        $validated = $request->validate($this->monthlyRecordRules());
        $duplicate = BankMonthlyRecord::query()
            ->where('bank_account_id', $validated['bank_account_id'])
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->where('id', '!=', $bankMonthlyRecord->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['month' => 'Rekod untuk bank dan bulan ini sudah wujud.'])
                ->withInput();
        }

        $bankMonthlyRecord->update($validated);

        return redirect()
            ->route('admin.bank-accounts.index', ['year' => $validated['year']])
            ->with('success', 'Rekod bulanan berjaya dikemaskini.');
    }

    public function destroyMonthlyRecord(BankMonthlyRecord $bankMonthlyRecord): RedirectResponse
    {
        $year = $bankMonthlyRecord->year;
        $bankMonthlyRecord->delete();

        return redirect()
            ->route('admin.bank-accounts.index', ['year' => $year])
            ->with('success', 'Rekod bulanan berjaya dipadam.');
    }

    /** @return array<string, string[]> */
    private function bankRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, mixed[]> */
    private function monthlyRecordRules(): array
    {
        return [
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
            'year' => ['required', 'integer', 'min:'.self::MIN_YEAR, 'max:'.self::MAX_YEAR],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'income' => ['required', 'numeric', 'min:0'],
            'expense' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function requestedYear(Request $request, int $fallback): int
    {
        $year = $request->integer('year', $fallback);

        return $year >= self::MIN_YEAR && $year <= self::MAX_YEAR ? $year : $fallback;
    }

    /** @return array<string, mixed> */
    private function serializeBank(BankAccount $bankAccount): array
    {
        $records = $bankAccount->monthlyRecords;
        $latestRecord = $records->sortBy('month')->last();

        return [
            'id' => $bankAccount->id,
            'name' => $bankAccount->name,
            'account_number' => $bankAccount->account_number,
            'account_holder' => $bankAccount->account_holder,
            'records_count' => (int) $bankAccount->monthly_records_count,
            'current_balance' => $latestRecord?->calculatedClosingBalance() ?? 0,
            'income' => round((float) $records->sum(fn (BankMonthlyRecord $record): float => (float) $record->income), 2),
            'expense' => round((float) $records->sum(fn (BankMonthlyRecord $record): float => (float) $record->expense), 2),
            'records' => $records->map(fn (BankMonthlyRecord $record): array => $this->serializeMonthlyRecord($record))->values(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeMonthlyRecord(BankMonthlyRecord $record): array
    {
        return [
            'id' => $record->id,
            'bank_account_id' => $record->bank_account_id,
            'year' => $record->year,
            'month' => $record->month,
            'opening_balance' => (float) $record->opening_balance,
            'income' => (float) $record->income,
            'expense' => (float) $record->expense,
            'closing_balance' => $record->calculatedClosingBalance(),
            'notes' => $record->notes,
        ];
    }
}
