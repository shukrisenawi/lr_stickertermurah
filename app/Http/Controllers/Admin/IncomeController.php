<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\IncomeCategory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class IncomeController extends Controller
{
    private const MAX_RECEIPT_SIZE_KB = 10240;

    public function index(): Response
    {
        return Inertia::render('Admin/Incomes/Index', [
            'incomes' => Income::query()->with(['creator:id,name,email', 'category:id,name'])
                ->latest('received_date')->latest('id')->paginate(20)->withQueryString()
                ->through(fn (Income $income): array => [
                    'id' => $income->id,
                    'income_category_id' => $income->income_category_id,
                    'description' => $income->description,
                    'amount' => (float) $income->amount,
                    'received_date' => $income->received_date?->toDateString(),
                    'notes' => $income->notes,
                    'receipt_original_name' => $income->receipt_original_name,
                    'receipt_mime_type' => $income->receipt_mime_type,
                    'receipt_file_size' => $income->receipt_file_size,
                    'receipt_url' => $income->receipt_path ? route('admin.incomes.receipt.download', $income) : null,
                    'receipt_preview_url' => $income->receipt_path && str_starts_with((string) $income->receipt_mime_type, 'image/')
                        ? route('admin.incomes.receipt.preview', $income) : null,
                    'created_at' => $income->created_at,
                    'creator' => $income->creator ? ['name' => $income->creator->name, 'email' => $income->creator->email] : null,
                    'category' => $income->category ? ['id' => $income->category->id, 'name' => $income->category->name] : null,
                ]),
            'totalAmount' => (float) Income::query()->sum('amount'),
            'totalCount' => Income::query()->count(),
            'today' => now()->toDateString(),
            'maxReceiptSizeMb' => (int) (self::MAX_RECEIPT_SIZE_KB / 1024),
            'categories' => IncomeCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request);
    }

    public function update(Request $request, Income $income): RedirectResponse
    {
        return $this->save($request, $income);
    }

    private function save(Request $request, ?Income $income = null): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'income_category_id' => ['required', 'integer', 'exists:income_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'received_date' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.self::MAX_RECEIPT_SIZE_KB],
        ]);
        unset($validated['receipt']);
        $receipt = $request->file('receipt');
        $oldReceiptPath = $income?->receipt_path;
        $storedPath = null;

        try {
            DB::transaction(function () use ($validated, $receipt, $request, $income, &$storedPath): void {
                $attributes = $validated;
                if ($receipt) {
                    $storedPath = $receipt->store('incomes/receipts', 'local');
                    if (! is_string($storedPath)) {
                        throw new \RuntimeException('Gagal menyimpan fail resit.');
                    }
                    $attributes += [
                        'receipt_path' => $storedPath,
                        'receipt_original_name' => $receipt->getClientOriginalName(),
                        'receipt_mime_type' => $receipt->getMimeType() ?: $receipt->getClientMimeType(),
                        'receipt_file_size' => $receipt->getSize() ?: 0,
                    ];
                }
                if ($income) {
                    $income->update($attributes);
                } else {
                    Income::query()->create($attributes + ['created_by' => $request->user()->id]);
                }
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            report($exception);

            return back()->withInput()->with('error', 'Rekod duit masuk tidak dapat disimpan. Sila cuba lagi.');
        }

        if ($storedPath && $oldReceiptPath && $oldReceiptPath !== $storedPath) {
            Storage::disk('local')->delete($oldReceiptPath);
        }

        return redirect()->route('admin.incomes.index')->with('success', $income
            ? 'Rekod duit masuk berjaya dikemaskini.' : 'Rekod duit masuk berjaya disimpan.');
    }

    public function downloadReceipt(Income $income)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($income->receipt_path && $disk->exists($income->receipt_path), 404);
        $name = basename(str_replace('\\', '/', (string) $income->receipt_original_name));

        return $disk->download($income->receipt_path, $name ?: 'resit-'.$income->id, [
            'Content-Type' => $income->receipt_mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function previewReceipt(Income $income)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($income->receipt_path && $disk->exists($income->receipt_path)
            && str_starts_with((string) $income->receipt_mime_type, 'image/'), 404);

        return response()->file($disk->path($income->receipt_path), [
            'Content-Type' => $income->receipt_mime_type,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Income $income): RedirectResponse
    {
        if ($income->receipt_path) {
            Storage::disk('local')->delete($income->receipt_path);
        }
        $income->delete();

        return back()->with('success', 'Rekod duit masuk berjaya dipadam.');
    }
}
