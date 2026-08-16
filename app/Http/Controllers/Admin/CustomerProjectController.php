<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomerProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());

        $projects = CustomerProject::query()
            ->with(['user', 'order'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('order', fn ($order) => $order->where('order_no', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (CustomerProject $project) => [
                'id' => $project->id,
                'title' => $project->title,
                'notes' => $project->notes,
                'preview_url' => $project->preview_path ? route('admin.projects.preview', $project) : null,
                'source_files' => collect($project->source_paths ?: [$project->source_path])
                    ->values()
                    ->map(fn (string $path, int $index) => [
                        'name' => basename($path),
                        'url' => route('admin.projects.source', ['project' => $project, 'source' => $index]),
                    ])
                    ->all(),
                'created_at' => $project->created_at,
                'user' => $project->user ? ['id' => $project->user->id, 'name' => $project->user->name, 'email' => $project->user->email] : null,
                'order' => $project->order ? ['id' => $project->order->id, 'order_no' => $project->order->order_no] : null,
            ]);

        return Inertia::render('Admin/Projects/Index', ['projects' => $projects, 'search' => $search]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Create', [
            'customers' => User::query()->where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email']),
            'orders' => Order::query()->with('user')->latest()->limit(100)->get(['id', 'user_id', 'order_no', 'customer_name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,zip,rar,7z,ai,psd,eps,pdf,svg', 'max:51200'],
        ]);

        if (! User::query()->whereKey($validated['user_id'])->where('is_admin', false)->exists()) {
            return back()->withErrors(['user_id' => 'Sila pilih akaun customer yang sah.'])->withInput();
        }

        if (! empty($validated['order_id'])) {
            $orderBelongsToCustomer = Order::query()->whereKey($validated['order_id'])->where('user_id', $validated['user_id'])->exists();
            if (! $orderBelongsToCustomer) {
                return back()->withErrors(['order_id' => 'Order tersebut bukan milik customer yang dipilih.'])->withInput();
            }
        }

        [$sourcePaths, $previewPaths] = $this->storeFiles($request->file('files'));

        CustomerProject::query()->create([
            'user_id' => $validated['user_id'],
            'order_id' => $validated['order_id'] ?? null,
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'preview_path' => $previewPaths[0] ?? '',
            'preview_paths' => $previewPaths,
            'source_path' => $sourcePaths[0],
            'source_paths' => $sourcePaths,
        ]);

        return redirect()->route('admin.projects.index')->with('success', 'Project customer berjaya disimpan.');
    }

    public function storeForOrder(Request $request, Order $order): RedirectResponse
    {
        if (! $order->user_id) {
            return back()->with('error', 'Order ini tiada akaun customer untuk dikaitkan dengan project.');
        }

        if (! $order->items()->exists()) {
            return back()->with('error', 'Order ini tiada item untuk dikaitkan dengan project.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,zip,rar,7z,ai,psd,eps,pdf,svg', 'max:51200'],
        ]);

        $item = $order->items()->firstOrFail();
        $currentProject = $item->customer_project_id
            ? CustomerProject::query()
                ->whereKey($item->customer_project_id)
                ->where('user_id', $order->user_id)
                ->first()
            : null;
        $existingSourcePaths = $currentProject
            ? collect($currentProject->source_paths ?: [$currentProject->source_path])->filter()->values()->all()
            : [];

        [$sourcePaths, $previewPaths] = $this->storeFiles($request->file('files'));

        if ($currentProject && (int) $currentProject->order_id === (int) $order->id) {
            $sourceIndices = $this->selectedSourceIndices($item, $existingSourcePaths);
            $newSourceIndices = range(count($existingSourcePaths), count($existingSourcePaths) + count($sourcePaths) - 1);
            $combinedSourcePaths = array_merge($existingSourcePaths, $sourcePaths);
            $combinedPreviewPaths = array_merge(
                collect($currentProject->preview_paths ?: [$currentProject->preview_path])->filter()->values()->all(),
                $previewPaths,
            );

            $currentProject->update([
                'preview_path' => $combinedPreviewPaths[0] ?? '',
                'preview_paths' => $combinedPreviewPaths,
                'source_path' => $combinedSourcePaths[0],
                'source_paths' => $combinedSourcePaths,
            ]);

            $this->attachProjectToOrder($order, $currentProject, array_merge($sourceIndices, $newSourceIndices));

            return back()->with('success', 'Fail baharu berjaya ditambah tanpa membuang fail yang telah dipilih.');
        }

        $selectedSourcePaths = $currentProject
            ? array_values(array_map(
                fn (int $sourceIndex): string => $existingSourcePaths[$sourceIndex],
                $this->selectedSourceIndices($item, $existingSourcePaths),
            ))
            : [];
        $copiedSourcePaths = $this->copySourceFiles($selectedSourcePaths);
        $combinedSourcePaths = array_merge($copiedSourcePaths, $sourcePaths);
        $selectedIndices = range(0, count($combinedSourcePaths) - 1);

        $project = CustomerProject::query()->create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'title' => $validated['title'],
            'preview_path' => $previewPaths[0] ?? '',
            'preview_paths' => $previewPaths,
            'source_path' => $combinedSourcePaths[0],
            'source_paths' => $combinedSourcePaths,
        ]);

        $this->attachProjectToOrder($order, $project, $selectedIndices);

        return back()->with('success', 'Fail project berjaya dimuat naik dan dikaitkan dengan order.');
    }

    public function selectForOrder(Request $request, Order $order): RedirectResponse
    {
        if (! $order->user_id) {
            return back()->with('error', 'Order ini tiada akaun customer untuk memilih project.');
        }

        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:customer_projects,id'],
            'source_indices' => ['present', 'array', 'max:20'],
            'source_indices.*' => ['required', 'integer', 'min:0'],
        ]);

        $project = CustomerProject::query()
            ->whereKey($validated['project_id'])
            ->where('user_id', $order->user_id)
            ->first();

        if (! $project) {
            return back()->withErrors(['project_id' => 'Project tersebut bukan milik customer order ini.']);
        }

        if (! $order->items()->exists()) {
            return back()->with('error', 'Order ini tiada item untuk dikaitkan dengan project.');
        }

        $sourcePaths = $project->source_paths ?: [$project->source_path];
        $sourceIndices = array_values(array_unique(array_map('intval', $validated['source_indices'])));
        foreach ($sourceIndices as $sourceIndex) {
            if (! array_key_exists($sourceIndex, $sourcePaths)) {
                return back()->withErrors(['source_indices' => 'Salah satu fail project yang dipilih tidak dijumpai.']);
            }
        }

        $this->attachProjectToOrder($order, $project, $sourceIndices);

        return back()->with('success', 'Project terdahulu berjaya dipilih untuk order ini.');
    }

    public function removeSourceFromOrder(Order $order, int $source): RedirectResponse
    {
        if (! $order->user_id) {
            return back()->with('error', 'Order ini tiada akaun customer untuk menguruskan fail project.');
        }

        $item = $order->items()->whereNotNull('customer_project_id')->first();
        $project = $item?->customer_project_id
            ? CustomerProject::query()
                ->whereKey($item->customer_project_id)
                ->where('user_id', $order->user_id)
                ->first()
            : null;

        if (! $item || ! $project) {
            return back()->with('error', 'Tiada project yang sedang digunakan untuk order ini.');
        }

        $sourcePaths = collect($project->source_paths ?: [$project->source_path])
            ->filter()
            ->values()
            ->all();

        if (! array_key_exists($source, $sourcePaths)) {
            return back()->withErrors(['source_index' => 'Fail project yang dipilih tidak dijumpai.']);
        }

        $sourceIndices = $item->customer_project_source_indices;
        if ($sourceIndices === null) {
            $sourceIndices = $item->customer_project_source_index !== null
                ? [(int) $item->customer_project_source_index]
                : array_keys($sourcePaths);
        }

        $sourceIndices = array_values(array_unique(array_map('intval', $sourceIndices)));
        if (! in_array($source, $sourceIndices, true)) {
            return back()->withErrors(['source_index' => 'Fail tersebut bukan sebahagian daripada pilihan order ini.']);
        }

        $sourceIndices = array_values(array_filter(
            $sourceIndices,
            fn (int $sourceIndex): bool => $sourceIndex !== $source && array_key_exists($sourceIndex, $sourcePaths),
        ));

        $item->update([
            'customer_project_source_index' => $sourceIndices[0] ?? null,
            'customer_project_source_indices' => $sourceIndices,
        ]);

        return back()->with('success', 'Fail berjaya dibuang daripada pilihan order.');
    }

    public function preview(CustomerProject $project)
    {
        abort_unless(Storage::exists($project->preview_path), 404);

        return response()->file(Storage::path($project->preview_path), [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }

    public function source(CustomerProject $project, ?int $source = null)
    {
        $sourcePaths = $project->source_paths ?: [$project->source_path];
        $sourcePath = $sourcePaths[$source ?? 0] ?? null;
        abort_unless($sourcePath && Storage::exists($sourcePath), 404);

        return Storage::download($sourcePath, basename($sourcePath));
    }

    public function sourcePreview(CustomerProject $project, ?int $source = null)
    {
        $sourcePaths = $project->source_paths ?: [$project->source_path];
        $sourcePath = $sourcePaths[$source ?? 0] ?? null;
        abort_unless($sourcePath && Storage::exists($sourcePath), 404);

        return response()->file(Storage::path($sourcePath), [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }

    public function destroy(CustomerProject $project): RedirectResponse
    {
        Storage::delete(array_merge($project->preview_paths ?: [$project->preview_path], $project->source_paths ?: [$project->source_path]));
        $project->delete();

        return back()->with('success', 'Project berjaya dipadam.');
    }

    private function storeFiles(array $files): array
    {
        $sourcePaths = [];
        $previewPaths = [];

        foreach ($files as $file) {
            $sourcePaths[] = $file->store('customer-projects/sources');
            if ($this->isPreviewableImage($file)) {
                $previewPaths[] = $this->makePreview($file);
            }
        }

        return [$sourcePaths, $previewPaths];
    }

    private function attachProjectToOrder(Order $order, CustomerProject $project, ?array $sourceIndices = null): void
    {
        $sourceIndices = $sourceIndices === null ? null : array_values(array_unique($sourceIndices));

        $order->items()->firstOrFail()->update([
            'sticker_design_id' => null,
            'customer_project_id' => $project->id,
            'customer_project_source_index' => $sourceIndices[0] ?? null,
            'customer_project_source_indices' => $sourceIndices,
            'custom_design_description' => $project->title,
        ]);
    }

    private function selectedSourceIndices(OrderItem $item, array $sourcePaths): array
    {
        $sourceIndices = $item->customer_project_source_indices;
        if ($sourceIndices === null) {
            $sourceIndices = $item->customer_project_source_index !== null
                ? [(int) $item->customer_project_source_index]
                : array_keys($sourcePaths);
        }

        return array_values(array_filter(
            array_unique(array_map('intval', $sourceIndices)),
            fn (int $sourceIndex): bool => array_key_exists($sourceIndex, $sourcePaths),
        ));
    }

    private function copySourceFiles(array $sourcePaths): array
    {
        return array_map(function (string $sourcePath): string {
            abort_unless(Storage::exists($sourcePath), 422, 'Fail project terdahulu tidak dapat dibaca.');

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $copyPath = 'customer-projects/sources/'.Str::uuid().($extension !== '' ? '.'.$extension : '');
            abort_unless(Storage::copy($sourcePath, $copyPath), 422, 'Fail project terdahulu tidak dapat disalin.');

            return $copyPath;
        }, $sourcePaths);
    }

    private function makePreview($file): string
    {
        $image = match ($file->getMimeType()) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
            default => false,
        };

        abort_if(! $image, 422, 'Gambar preview tidak dapat dibaca.');

        $width = imagesx($image);
        $height = imagesy($image);
        $targetHeight = min(250, max(1, $height));
        $targetWidth = max(1, (int) round($width * $targetHeight / $height));
        $preview = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($preview, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        imagealphablending($preview, true);
        $textColor = imagecolorallocatealpha($preview, 255, 255, 255, 65);
        imagestring($preview, 5, max(4, (int) ($targetWidth / 2) - 55), max(4, (int) ($targetHeight / 2) - 8), 'StickerTermurah', $textColor);

        $path = 'customer-projects/previews/'.uniqid('preview_', true).'.jpg';
        $absolutePath = Storage::path($path);
        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }
        imagejpeg($preview, $absolutePath, 55);
        imagedestroy($preview);

        return $path;
    }

    private function isPreviewableImage($file): bool
    {
        return in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true);
    }
}
