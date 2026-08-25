<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\Setting;
use App\Support\ImageOptimizer;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString() === 'completed' ? 'completed' : 'pending';

        $orders = Order::query()
            ->with(['user', 'invoice'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($status === 'completed', function ($query) {
                $query->where('status', 'completed');
            })
            ->when($status === 'pending', function ($query) {
                $query->where('status', '!=', 'completed');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', $this->showProps($order, false));
    }

    public function edit(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', $this->showProps($order, true));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order berjaya dipadam.');
    }

    public function update(Request $request, Order $order): Response
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,processing,shipped,completed,cancelled'],
            'tracking_no' => ['nullable', 'string', 'max:50'],
        ]);

        $previousTrackingNo = trim((string) $order->tracking_no);
        $trackingNo = trim((string) ($validated['tracking_no'] ?? ''));

        $order->update([
            'status' => $trackingNo !== '' ? 'completed' : $validated['status'],
            'tracking_no' => $trackingNo !== '' ? $trackingNo : null,
        ]);

        if ($trackingNo !== '' && $trackingNo !== $previousTrackingNo) {
            $this->sendTrackingNotification($order);
        }

        return Inertia::render('Admin/Orders/Show', $this->showProps($order, false))
            ->with('success', 'Order berjaya dikemaskini.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_no' => ['required', 'string', 'max:50'],
        ]);

        $previousTrackingNo = trim((string) $order->tracking_no);
        $trackingNo = trim($validated['tracking_no']);

        $order->update([
            'status' => 'completed',
            'tracking_no' => $trackingNo,
        ]);

        if ($trackingNo !== $previousTrackingNo) {
            $this->sendTrackingNotification($order);
        }

        return back()->with('success', 'No. tracking berjaya disimpan. Status order ditetapkan sebagai completed.');
    }

    public function uploadItemFiles(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToOrder($order, $item);

        $validated = $request->validate([
            'source_file' => ['nullable', 'file', 'max:51200'],
            'preview_image' => ['nullable', 'image', 'max:10240'],
        ]);

        if (! $request->hasFile('source_file') && ! $request->hasFile('preview_image')) {
            return back()->withErrors(['source_file' => 'Pilih fail source atau gambar preview untuk item ini.']);
        }

        $updates = [];
        $oldPaths = [];

        if ($request->hasFile('source_file')) {
            $updates['admin_source_path'] = $request->file('source_file')->store('order-items/sources', 'local');
            if ($item->admin_source_path) {
                $oldPaths[] = $item->admin_source_path;
            }
        }

        if ($request->hasFile('preview_image')) {
            $updates['customer_preview_path'] = ImageOptimizer::store(
                $request->file('preview_image'),
                'order-items/previews',
                1600,
                1600,
                70,
                'local',
            );
            if ($item->customer_preview_path) {
                $oldPaths[] = $item->customer_preview_path;
            }
        }

        $item->update($updates);

        foreach ($oldPaths as $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return back()->with('success', 'Fail item berjaya dimuat naik.');
    }

    public function itemSource(Order $order, OrderItem $item)
    {
        $this->ensureItemBelongsToOrder($order, $item);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($item->admin_source_path && $disk->exists($item->admin_source_path), 404);

        return $disk->download($item->admin_source_path, basename($item->admin_source_path));
    }

    public function itemPreview(Order $order, OrderItem $item)
    {
        $this->ensureItemBelongsToOrder($order, $item);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($item->customer_preview_path && $disk->exists($item->customer_preview_path), 404);

        $path = $disk->path($item->customer_preview_path);

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'image/webp',
        ]);
    }

    public function itemPreviewDownload(Order $order, OrderItem $item)
    {
        $this->ensureItemBelongsToOrder($order, $item);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($item->customer_preview_path && $disk->exists($item->customer_preview_path), 404);

        return $disk->download($item->customer_preview_path, 'preview-'.$item->id.'.webp');
    }

    public function quote(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'price_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($order->invoice) {
            return back()->with('error', 'Harga tidak boleh diubah selepas invoice dicipta.');
        }

        $order->loadMissing('items');
        $items = $order->items->values();

        if ($items->isEmpty()) {
            return back()->with('error', 'Order ini tiada item untuk ditetapkan harga.');
        }

        $amount = round((float) $validated['amount'], 2);
        $deposit = min((float) (PaymentSetting::query()->value('deposit_amount') ?? 20), $amount);

        $weights = $items->map(fn ($item): float => max(0, (float) ($item->line_total ?? 0)));
        if ($weights->sum() <= 0) {
            $weights = $items->map(fn ($item): float => max(1, (int) $item->quantity));
        }
        $totalWeight = $weights->sum();
        $remainingAmount = $amount;
        $lastIndex = $items->count() - 1;

        foreach ($items as $index => $item) {
            $lineTotal = $index === $lastIndex
                ? $remainingAmount
                : round($amount * ($weights[$index] / $totalWeight), 2);
            $remainingAmount = round($remainingAmount - $lineTotal, 2);

            $item->update([
                'unit_price' => round($lineTotal / max(1, $item->quantity), 2),
                'line_total' => $lineTotal,
            ]);
        }

        $order->update([
            'subtotal' => $amount,
            'total' => $amount,
            'deposit_amount' => $deposit,
            'balance_due' => max(0, $amount - $deposit),
            'payment_status' => 'pending',
            'pricing_status' => 'awaiting_customer_approval',
            'price_note' => $validated['price_note'] ?? null,
            'price_quoted_at' => now(),
            'price_approved_at' => null,
        ]);

        return back()->with('success', 'Harga berjaya dihantar kepada customer untuk kelulusan.');
    }

    private function showProps(Order $order, bool $editMode): array
    {
        $order->load(['items.design', 'items.project', 'items.size', 'user', 'invoice']);
        $order->items->each(function (OrderItem $item): void {
            $item->setAttribute('has_admin_source', filled($item->admin_source_path));
            $item->setAttribute('has_customer_preview', filled($item->customer_preview_path));
        });

        return [
            'order' => $order,
            'editMode' => $editMode,
            'uploadedFiles' => $editMode ? [] : $this->uploadedFilesForOrder($order),
        ];
    }

    private function uploadedFilesForOrder(Order $order): array
    {
        $designFiles = $order->items
            ->values()
            ->flatMap(function (OrderItem $item, int $itemIndex): array {
                $paths = $item->customer_design_paths ?: [$item->customer_design_path];
                $itemLabel = $this->itemReference($item, $itemIndex);

                return collect($paths)
                    ->filter()
                    ->values()
                    ->map(function (string $path, int $fileIndex) use ($itemIndex, $itemLabel): array {
                        $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);

                        return [
                            'id' => $itemIndex.'-'.$fileIndex,
                            'item_label' => $itemLabel,
                            'name' => basename($path),
                            'url' => url('storage/'.$path),
                            'preview_url' => null,
                            'is_image' => $isImage,
                        ];
                    })
                    ->all();
            });

        $projectItemReferences = $order->items
            ->values()
            ->filter(fn (OrderItem $item): bool => $item->project !== null)
            ->mapWithKeys(fn (OrderItem $item, int $itemIndex): array => [
                $item->project->id => $this->itemReference($item, $itemIndex),
            ]);

        $projects = $order->items
            ->map(fn ($item) => $item->project)
            ->filter()
            ->merge(CustomerProject::query()->where('order_id', $order->id)->get())
            ->unique('id');

        $projectFiles = $projects
            ->flatMap(function (CustomerProject $project) use ($projectItemReferences): array {
                $paths = collect($project->source_paths ?: [$project->source_path])
                    ->filter()
                    ->values();

                return $paths->map(function (string $path, int $fileIndex) use ($project, $projectItemReferences): array {
                    $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);

                    return [
                        'id' => 'project-'.$project->id.'-'.$fileIndex,
                        'item_label' => $projectItemReferences->get($project->id, 'Project - '.$project->title),
                        'name' => basename($path),
                        'url' => route('admin.projects.source', ['project' => $project, 'source' => $fileIndex]),
                        'preview_url' => $isImage
                            ? route('admin.projects.source-preview', ['project' => $project, 'source' => $fileIndex])
                            : null,
                        'is_image' => $isImage,
                    ];
                })->all();
            });

        $previewFiles = $order->items
            ->values()
            ->filter(fn (OrderItem $item): bool => filled($item->customer_preview_path))
            ->map(function (OrderItem $item, int $itemIndex) use ($order): array {
                $previewUrl = route('admin.orders.items.preview', ['order' => $order, 'item' => $item]);

                return [
                    'id' => 'item-preview-'.$item->id,
                    'item_label' => $this->itemReference($item, $itemIndex),
                    'name' => 'Gambar preview design',
                    'url' => $previewUrl,
                    'download_url' => route('admin.orders.items.preview-download', ['order' => $order, 'item' => $item]),
                    'preview_url' => $previewUrl,
                    'is_image' => true,
                ];
            });

        return $designFiles
            ->merge($previewFiles)
            ->merge($projectFiles)
            ->values()
            ->all();
    }

    private function ensureItemBelongsToOrder(Order $order, OrderItem $item): void
    {
        abort_unless((int) $item->order_id === (int) $order->id, 404);
    }

    private function itemReference(OrderItem $item, int $itemIndex): string
    {
        $design = $item->design?->name
            ?: $item->project?->title
            ?: $item->custom_design_description
            ?: 'Design sendiri';
        $size = $item->size?->name ?: $item->requested_size ?: 'Saiz custom';

        return 'Bil. '.($itemIndex + 1).' - '.$design.' | '.$size.' | Qty '.(int) $item->quantity;
    }

    private function sendTrackingNotification(Order $order): void
    {
        $webhookUrl = Setting::getValue('n8n_webhook_url');
        if (! $webhookUrl) {
            return;
        }

        $recipientPhone = preg_replace('/\D+/', '', $order->customer_phone) ?: '';
        if (str_starts_with($recipientPhone, '0')) {
            $recipientPhone = '60'.substr($recipientPhone, 1);
        } elseif ($recipientPhone !== '' && ! str_starts_with($recipientPhone, '60')) {
            $recipientPhone = '60'.$recipientPhone;
        }

        $message = "No. tracking order anda telah dikemaskini.\n\n"
            ."No. Order: {$order->order_no}\n"
            ."No. Tracking: {$order->tracking_no}\n"
            ."Status: completed\n\n"
            .'Semak status order: '.url('/semak-order');

        try {
            $response = Http::timeout(10)->post($webhookUrl, [
                'type' => 'tracking_updated',
                'event' => 'order_tracking_updated',
                'message' => $message,
                'order_no' => $order->order_no,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'recipient_phone' => $recipientPhone,
                'phone' => $recipientPhone,
                'tracking_no' => $order->tracking_no,
                'status' => 'completed',
            ]);

            if ($response->failed()) {
                Log::warning('N8n tracking notification failed.', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('N8n tracking notification failed: '.$e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }
}
