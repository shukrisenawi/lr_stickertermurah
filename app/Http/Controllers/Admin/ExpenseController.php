<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ExpenseController extends Controller
{
    private const MAX_RECEIPT_SIZE_KB = 10240;

    public function index(): Response
    {
        $expenses = Expense::query()
            ->with(['creator:id,name,email', 'category:id,name'])
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Expense $expense): array => $this->serializeExpense($expense));

        return Inertia::render('Admin/Expenses/Index', [
            'expenses' => $expenses,
            'totalAmount' => (float) Expense::query()->sum('amount'),
            'totalCount' => Expense::query()->count(),
            'today' => now()->toDateString(),
            'maxReceiptSizeMb' => (int) (self::MAX_RECEIPT_SIZE_KB / 1024),
            'categories' => ExpenseCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'purchase_date' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.self::MAX_RECEIPT_SIZE_KB],
        ]);

        $receipt = $request->file('receipt');
        $storedPath = null;

        try {
            DB::transaction(function () use ($validated, $receipt, $request, &$storedPath): void {
                if ($receipt) {
                    $storedPath = $receipt->store('expenses/receipts', 'local');

                    if (! is_string($storedPath)) {
                        throw new \RuntimeException('Gagal menyimpan fail resit.');
                    }
                }

                Expense::query()->create([
                    'created_by' => $request->user()->id,
                    'expense_category_id' => $validated['expense_category_id'],
                    'description' => $validated['description'],
                    'amount' => $validated['amount'],
                    'purchase_date' => $validated['purchase_date'],
                    'notes' => $validated['notes'] ?? null,
                    'receipt_path' => $storedPath,
                    'receipt_original_name' => $receipt?->getClientOriginalName(),
                    'receipt_mime_type' => $receipt?->getMimeType() ?: $receipt?->getClientMimeType(),
                    'receipt_file_size' => $receipt?->getSize() ?: 0,
                ]);
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            report($exception);

            return back()->withInput()->with('error', 'Rekod duit keluar tidak dapat disimpan. Sila cuba lagi.');
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Rekod duit keluar berjaya disimpan.');
    }

    public function downloadReceipt(Expense $expense)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($expense->receipt_path && $disk->exists($expense->receipt_path), 404);

        $downloadName = basename(str_replace('\\', '/', (string) $expense->receipt_original_name));

        return $disk->download($expense->receipt_path, $downloadName ?: 'resit-'.$expense->id, [
            'Content-Type' => $expense->receipt_mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function previewReceipt(Expense $expense)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless(
            $expense->receipt_path
                && $disk->exists($expense->receipt_path)
                && str_starts_with((string) $expense->receipt_mime_type, 'image/'),
            404,
        );

        return response()->file($disk->path($expense->receipt_path), [
            'Content-Type' => $expense->receipt_mime_type,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->receipt_path) {
            Storage::disk('local')->delete($expense->receipt_path);
        }

        $expense->delete();

        return back()->with('success', 'Rekod duit keluar berjaya dipadam.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeExpense(Expense $expense): array
    {
        return [
            'id' => $expense->id,
            'expense_category_id' => $expense->expense_category_id,
            'description' => $expense->description,
            'amount' => (float) $expense->amount,
            'purchase_date' => $expense->purchase_date?->toDateString(),
            'notes' => $expense->notes,
            'receipt_original_name' => $expense->receipt_original_name,
            'receipt_mime_type' => $expense->receipt_mime_type,
            'receipt_file_size' => $expense->receipt_file_size,
            'receipt_url' => $expense->receipt_path
                ? route('admin.expenses.receipt.download', $expense)
                : null,
            'receipt_preview_url' => $expense->receipt_path && str_starts_with((string) $expense->receipt_mime_type, 'image/')
                ? route('admin.expenses.receipt.preview', $expense)
                : null,
            'created_at' => $expense->created_at,
            'creator' => $expense->creator ? [
                'name' => $expense->creator->name,
                'email' => $expense->creator->email,
            ] : null,
            'category' => $expense->category ? [
                'id' => $expense->category->id,
                'name' => $expense->category->name,
            ] : null,
        ];
    }
}
