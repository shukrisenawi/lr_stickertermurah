<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankStatement;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class BankStatementController extends Controller
{
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
