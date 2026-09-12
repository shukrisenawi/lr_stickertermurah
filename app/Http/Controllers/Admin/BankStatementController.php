<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankStatement;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BankStatementController extends Controller
{
    private const MIN_YEAR = 2000;

    private const MAX_YEAR = 2100;

    public function store(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:'.self::MIN_YEAR, 'max:'.self::MAX_YEAR],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:'.BankStatement::MAX_FILE_SIZE_KB],
        ]);

        $file = $request->file('file');
        $storedPath = null;

        try {
            DB::transaction(function () use ($validated, $file, $bankAccount, $request, &$storedPath): void {
                $storedPath = $file->store('bank-statements/'.$bankAccount->id, 'local');

                if (! is_string($storedPath)) {
                    throw new \RuntimeException('Gagal menyimpan fail penyata bank.');
                }

                BankStatement::query()->create([
                    'bank_account_id' => $bankAccount->id,
                    'uploaded_by' => $request->user()->id,
                    'year' => $validated['year'],
                    'month' => $validated['month'],
                    'file_path' => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                    'file_size' => $file->getSize() ?: 0,
                ]);
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            report($exception);

            return back()->withInput()->with('error', 'Penyata bank tidak dapat disimpan. Sila cuba lagi.');
        }

        return redirect()
            ->route('admin.bank-accounts.index', ['year' => $validated['year']])
            ->with('success', 'Penyata bank berjaya dimuat naik.');
    }

    public function download(BankStatement $bankStatement)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($disk->exists($bankStatement->file_path), 404);

        $downloadName = basename(str_replace('\\', '/', (string) $bankStatement->original_name));

        return $disk->download($bankStatement->file_path, $downloadName ?: 'penyata-bank-'.$bankStatement->id, [
            'Content-Type' => $bankStatement->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function preview(BankStatement $bankStatement)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless(
            $disk->exists($bankStatement->file_path)
                && str_starts_with((string) $bankStatement->mime_type, 'image/'),
            404,
        );

        return response()->file($disk->path($bankStatement->file_path), [
            'Content-Type' => $bankStatement->mime_type,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(BankStatement $bankStatement): RedirectResponse
    {
        $year = $bankStatement->year;
        Storage::disk('local')->delete($bankStatement->file_path);
        $bankStatement->delete();

        return redirect()
            ->route('admin.bank-accounts.index', ['year' => $year])
            ->with('success', 'Penyata bank berjaya dipadam.');
    }
}
