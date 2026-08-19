<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyDocument;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CompanyDocumentController extends Controller
{
    private const CATEGORIES = [
        'ssm' => 'SSM',
        'receipt' => 'Resit',
        'license' => 'Lesen',
        'certificate' => 'Sijil',
        'bank' => 'Bank',
        'tax' => 'Cukai',
        'other' => 'Lain-lain',
    ];

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $category = $request->string('category')->toString();
        if (! array_key_exists($category, self::CATEGORIES)) {
            $category = '';
        }

        $documents = CompanyDocument::query()
            ->with('uploader:id,name,email')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('original_name', 'like', '%'.$search.'%')
                        ->orWhere('notes', 'like', '%'.$search.'%');
                });
            })
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (CompanyDocument $document): array => $this->serializeDocument($document));

        return Inertia::render('Admin/CompanyDocuments/Index', [
            'documents' => $documents,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
            'categories' => collect(self::CATEGORIES)
                ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
                ->values(),
            'maxFileSizeMb' => 20,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(self::CATEGORIES))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store('company-documents', 'local');

        if (! is_string($path)) {
            return back()->with('error', 'Dokumen tidak dapat dimuat naik. Sila cuba lagi.');
        }

        try {
            DB::transaction(function () use ($validated, $file, $path, $request): void {
                CompanyDocument::query()->create([
                    'uploaded_by' => $request->user()->id,
                    'title' => $validated['title'],
                    'category' => $validated['category'],
                    'notes' => $validated['notes'] ?? null,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                    'file_size' => $file->getSize() ?: 0,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            report($exception);

            return back()->with('error', 'Dokumen tidak dapat disimpan. Sila cuba lagi.');
        }

        return redirect()->route('admin.company-documents.index')
            ->with('success', 'Dokumen syarikat berjaya disimpan.');
    }

    public function download(CompanyDocument $companyDocument)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($disk->exists($companyDocument->file_path), 404);

        $downloadName = basename(str_replace('\\', '/', $companyDocument->original_name));

        return $disk->download($companyDocument->file_path, $downloadName, [
            'Content-Type' => $companyDocument->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(CompanyDocument $companyDocument): RedirectResponse
    {
        Storage::disk('local')->delete($companyDocument->file_path);
        $companyDocument->delete();

        return back()->with('success', 'Dokumen syarikat berjaya dipadam.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDocument(CompanyDocument $document): array
    {
        return [
            'id' => $document->id,
            'title' => $document->title,
            'category' => $document->category,
            'category_label' => self::CATEGORIES[$document->category] ?? 'Lain-lain',
            'notes' => $document->notes,
            'original_name' => $document->original_name,
            'mime_type' => $document->mime_type,
            'file_size' => $document->file_size,
            'download_url' => route('admin.company-documents.download', $document),
            'created_at' => $document->created_at,
            'uploader' => $document->uploader ? [
                'name' => $document->uploader->name,
                'email' => $document->uploader->email,
            ] : null,
        ];
    }
}
