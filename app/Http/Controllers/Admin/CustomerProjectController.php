<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                'preview_url' => route('admin.projects.preview', $project),
                'source_name' => basename($project->source_path),
                'source_url' => route('admin.projects.source', $project),
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
            'preview' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'source' => ['required', 'file', 'mimes:zip,rar,7z,ai,psd,eps,pdf,svg', 'max:51200'],
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

        $previewPath = $this->makePreview($request->file('preview'));
        $sourcePath = $request->file('source')->store('customer-projects/sources');

        CustomerProject::query()->create([
            'user_id' => $validated['user_id'],
            'order_id' => $validated['order_id'] ?? null,
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'preview_path' => $previewPath,
            'source_path' => $sourcePath,
        ]);

        return redirect()->route('admin.projects.index')->with('success', 'Project customer berjaya disimpan.');
    }

    public function preview(CustomerProject $project)
    {
        abort_unless(Storage::exists($project->preview_path), 404);

        return response()->file(Storage::path($project->preview_path));
    }

    public function source(CustomerProject $project)
    {
        abort_unless(Storage::exists($project->source_path), 404);

        return Storage::download($project->source_path, basename($project->source_path));
    }

    public function destroy(CustomerProject $project): RedirectResponse
    {
        Storage::delete([$project->preview_path, $project->source_path]);
        $project->delete();

        return back()->with('success', 'Project berjaya dipadam.');
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
}
