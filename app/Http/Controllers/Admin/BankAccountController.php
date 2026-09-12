<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankMonthlyRecord;
use App\Models\BankStatement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class BankAccountController extends Controller
{
    private const MIN_YEAR = 2000;

    private const MAX_YEAR = 2100;

    public function index(Request $request): Response
    {
        $currentYear = now()->year;
        $year = $this->requestedYear($request, $currentYear);
        $bankAccounts = BankAccount::query()
            ->withCount(['monthlyRecords', 'statements'])
            ->with(['monthlyRecords' => fn ($query) => $query
                ->where('year', '<=', $year)
                ->orderBy('year')
                ->orderBy('month')])
            ->with(['statements' => fn ($query) => $query
                ->where('year', $year)
                ->latest('id')])
            ->orderBy('name')
            ->get();

        $banks = $bankAccounts->map(fn (BankAccount $bankAccount): array => $this->serializeBank($bankAccount, $year))->values();

        return Inertia::render('Admin/BankAccounts/Index', [
            'banks' => $banks,
            'year' => $year,
            'currentYear' => $currentYear,
            'currentMonth' => now()->month,
            'maxStatementSizeMb' => (int) (BankStatement::MAX_FILE_SIZE_KB / 1024),
            'years' => BankMonthlyRecord::query()
                ->select('year')
                ->distinct()
                ->pluck('year')
                ->merge(BankStatement::query()
                    ->select('year')
                    ->distinct()
                    ->pluck('year'))
                ->map(fn ($storedYear): int => (int) $storedYear)
                ->push($currentYear)
                ->push($year)
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
        $this->refreshOpeningBalances($bankAccount);

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Akaun bank berjaya dikemaskini.');
    }

    public function destroyBank(BankAccount $bankAccount): RedirectResponse
    {
        if ($bankAccount->monthlyRecords()->exists()) {
            return back()->with('error', 'Akaun bank yang mempunyai rekod bulanan tidak boleh dipadam.');
        }

        if ($bankAccount->statements()->exists()) {
            return back()->with('error', 'Akaun bank yang mempunyai bank statement tidak boleh dipadam.');
        }

        $bankAccount->delete();

        return back()->with('success', 'Akaun bank berjaya dipadam.');
    }

    public function storeMonthlyRecord(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->monthlyRecordRules());
        $bankAccount = BankAccount::query()->findOrFail($validated['bank_account_id']);
        $year = (int) $validated['year'];
        $month = (int) $validated['month'];
        $statement = $request->file('statement');
        $uploadedBy = (int) $request->user()->id;
        $storedStatementPath = null;

        try {
            DB::transaction(function () use ($validated, $bankAccount, $year, $month, $statement, $uploadedBy, &$storedStatementPath): void {
                BankMonthlyRecord::query()->updateOrCreate(
                    [
                        'bank_account_id' => $bankAccount->id,
                        'year' => $year,
                        'month' => $month,
                    ],
                    [
                        'opening_balance' => $this->balanceBefore($bankAccount, $year, $month),
                        'income' => $validated['income'],
                        'expense' => $validated['expense'],
                        'notes' => $validated['notes'] ?? null,
                    ],
                );

                if ($statement instanceof UploadedFile) {
                    $storedStatementPath = $this->storeStatement($bankAccount, $statement, $year, $month, $uploadedBy);
                }

                $this->refreshOpeningBalances($bankAccount, $year);
            });
        } catch (Throwable $exception) {
            if ($storedStatementPath) {
                Storage::disk('local')->delete($storedStatementPath);
            }

            report($exception);

            return back()->withInput()->with('error', 'Rekod bulanan tidak dapat disimpan. Sila cuba lagi.');
        }

        return redirect()
            ->route('admin.bank-accounts.index', ['year' => $year])
            ->with('success', 'Rekod bulanan berjaya disimpan.');
    }

    public function updateMonthlyRecord(Request $request, BankMonthlyRecord $bankMonthlyRecord): RedirectResponse
    {
        $validated = $request->validate($this->monthlyRecordRules());
        $oldBankAccountId = $bankMonthlyRecord->bank_account_id;
        $oldYear = $bankMonthlyRecord->year;
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

        $bankAccount = BankAccount::query()->findOrFail($validated['bank_account_id']);
        $year = (int) $validated['year'];
        $month = (int) $validated['month'];
        $statement = $request->file('statement');
        $uploadedBy = (int) $request->user()->id;
        $storedStatementPath = null;

        try {
            DB::transaction(function () use ($validated, $bankMonthlyRecord, $bankAccount, $year, $month, $statement, $uploadedBy, $oldBankAccountId, $oldYear, &$storedStatementPath): void {
                $bankMonthlyRecord->update([
                    'bank_account_id' => $bankAccount->id,
                    'year' => $year,
                    'month' => $month,
                    'opening_balance' => $this->balanceBefore($bankAccount, $year, $month, $bankMonthlyRecord->id),
                    'income' => $validated['income'],
                    'expense' => $validated['expense'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                if ($statement instanceof UploadedFile) {
                    $storedStatementPath = $this->storeStatement($bankAccount, $statement, $year, $month, $uploadedBy);
                }

                $this->refreshOpeningBalances($bankAccount, $year);

                if ($oldBankAccountId !== $bankAccount->id || $oldYear !== $year) {
                    $oldBankAccount = BankAccount::query()->find($oldBankAccountId);

                    if ($oldBankAccount) {
                        $this->refreshOpeningBalances($oldBankAccount, $oldYear);
                    }
                }
            });
        } catch (Throwable $exception) {
            if ($storedStatementPath) {
                Storage::disk('local')->delete($storedStatementPath);
            }

            report($exception);

            return back()->withInput()->with('error', 'Rekod bulanan tidak dapat dikemaskini. Sila cuba lagi.');
        }

        return redirect()
            ->route('admin.bank-accounts.index', ['year' => $year])
            ->with('success', 'Rekod bulanan berjaya dikemaskini.');
    }

    public function destroyMonthlyRecord(BankMonthlyRecord $bankMonthlyRecord): RedirectResponse
    {
        $bankAccount = $bankMonthlyRecord->bankAccount;
        $year = $bankMonthlyRecord->year;
        $bankMonthlyRecord->delete();
        $this->refreshOpeningBalances($bankAccount, $year);

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
            'previous_year_balance' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, mixed[]> */
    private function monthlyRecordRules(): array
    {
        return [
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
            'year' => ['required', 'integer', 'min:'.self::MIN_YEAR, 'max:'.self::MAX_YEAR],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'income' => ['required', 'numeric', 'min:0'],
            'expense' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'statement' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:'.BankStatement::MAX_FILE_SIZE_KB],
        ];
    }

    private function requestedYear(Request $request, int $fallback): int
    {
        $year = $request->integer('year', $fallback);

        return $year >= self::MIN_YEAR && $year <= self::MAX_YEAR ? $year : $fallback;
    }

    /** @return array<string, mixed> */
    private function serializeBank(BankAccount $bankAccount, int $year): array
    {
        $records = $bankAccount->monthlyRecords
            ->sortBy(fn (BankMonthlyRecord $record): string => sprintf('%04d-%02d', $record->year, $record->month))
            ->values();
        $yearRecords = [];
        $yearIncome = 0.0;
        $yearExpense = 0.0;
        $balance = (float) $bankAccount->previous_year_balance;
        $yearStartingBalance = null;

        foreach ($records as $record) {
            if ($record->year === $year && $yearStartingBalance === null) {
                $yearStartingBalance = $balance;
            }

            $balance = round($balance + (float) $record->income - (float) $record->expense, 2);

            if ($record->year !== $year) {
                continue;
            }

            $yearIncome += (float) $record->income;
            $yearExpense += (float) $record->expense;
            $yearRecords[] = $this->serializeMonthlyRecord($record, $balance);
        }

        $yearStartingBalance ??= $balance;
        $latestRecord = $yearRecords[count($yearRecords) - 1] ?? null;

        return [
            'id' => $bankAccount->id,
            'name' => $bankAccount->name,
            'account_number' => $bankAccount->account_number,
            'account_holder' => $bankAccount->account_holder,
            'previous_year_balance' => (float) $bankAccount->previous_year_balance,
            'year_starting_balance' => round($yearStartingBalance, 2),
            'records_count' => (int) $bankAccount->monthly_records_count,
            'statements_count' => (int) $bankAccount->statements_count,
            'current_balance' => $latestRecord['closing_balance'] ?? round($balance, 2),
            'income' => round($yearIncome, 2),
            'expense' => round($yearExpense, 2),
            'records' => $yearRecords,
            'statements' => $bankAccount->statements
                ->map(fn (BankStatement $statement): array => $this->serializeStatement($statement))
                ->values(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeMonthlyRecord(BankMonthlyRecord $record, float $closingBalance): array
    {
        return [
            'id' => $record->id,
            'bank_account_id' => $record->bank_account_id,
            'year' => $record->year,
            'month' => $record->month,
            'income' => (float) $record->income,
            'expense' => (float) $record->expense,
            'closing_balance' => $closingBalance,
            'notes' => $record->notes,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeStatement(BankStatement $statement): array
    {
        return [
            'id' => $statement->id,
            'year' => $statement->year,
            'month' => $statement->month,
            'original_name' => $statement->original_name,
            'mime_type' => $statement->mime_type,
            'file_size' => $statement->file_size,
            'download_url' => route('admin.bank-statements.download', $statement),
            'preview_url' => str_starts_with((string) $statement->mime_type, 'image/')
                ? route('admin.bank-statements.preview', $statement)
                : null,
        ];
    }

    private function storeStatement(BankAccount $bankAccount, UploadedFile $file, int $year, int $month, int $uploadedBy): string
    {
        $path = $file->store('bank-statements/'.$bankAccount->id, 'local');

        if (! is_string($path)) {
            throw new \RuntimeException('Gagal menyimpan fail penyata bank.');
        }

        BankStatement::query()->create([
            'bank_account_id' => $bankAccount->id,
            'uploaded_by' => $uploadedBy,
            'year' => $year,
            'month' => $month,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'file_size' => $file->getSize() ?: 0,
        ]);

        return $path;
    }

    private function balanceBefore(BankAccount $bankAccount, int $year, int $month, ?int $excludedRecordId = null): float
    {
        $records = BankMonthlyRecord::query()
            ->where('bank_account_id', $bankAccount->id)
            ->when($excludedRecordId, fn ($query) => $query->where('id', '!=', $excludedRecordId))
            ->where(function ($query) use ($year, $month): void {
                $query
                    ->where('year', '<', $year)
                    ->orWhere(fn ($query) => $query
                        ->where('year', $year)
                        ->where('month', '<', $month));
            })
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        $balance = (float) $bankAccount->previous_year_balance;

        foreach ($records as $record) {
            $balance = round($balance + (float) $record->income - (float) $record->expense, 2);
        }

        return $balance;
    }

    private function refreshOpeningBalances(BankAccount $bankAccount, ?int $year = null): void
    {
        $years = $year === null
            ? BankMonthlyRecord::query()
                ->where('bank_account_id', $bankAccount->id)
                ->select('year')
                ->distinct()
                ->pluck('year')
            : collect([$year]);

        foreach ($years as $recordYear) {
            $records = BankMonthlyRecord::query()
                ->where('bank_account_id', $bankAccount->id)
                ->where('year', $recordYear)
                ->orderBy('month')
                ->get();
            $balance = $this->balanceBefore($bankAccount, (int) $recordYear, 1);

            foreach ($records as $record) {
                $record->updateQuietly(['opening_balance' => $balance]);
                $balance = round($balance + (float) $record->income - (float) $record->expense, 2);
            }
        }
    }
}
