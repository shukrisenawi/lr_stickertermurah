<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\Setting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Services\ShippingService;
use App\Support\ImageOptimizer;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
        return Inertia::render('Admin/Orders/Show', $this->showProps($order, false, true));
    }

    public function edit(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', $this->showProps($order, true, true));
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
            'status' => $trackingNo !== '' ? 'shipped' : $validated['status'],
            'tracking_no' => $trackingNo !== '' ? $trackingNo : null,
        ]);

        if ($trackingNo !== '' && $trackingNo !== $previousTrackingNo) {
            $this->sendTrackingNotification($order);
        }

        return Inertia::render('Admin/Orders/Show', $this->showProps($order, false, true))
            ->with('success', 'Order berjaya dikemaskini.');
    }

    public function updateItem(Request $request, Order $order, OrderItem $item, ShippingService $shippingService): RedirectResponse
    {
        $this->ensureItemBelongsToOrder($order, $item);

        $validated = $request->validate([
            'design_id' => ['nullable', 'integer', 'exists:sticker_designs,id'],
            'project_id' => ['nullable', 'integer', 'exists:customer_projects,id'],
            'size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'custom_design_description' => ['nullable', 'string', 'max:2000'],
            'requested_size' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cut_type' => ['required', Rule::in(['standard', 'die-cut'])],
        ]);

        if (! empty($validated['design_id']) && ! empty($validated['project_id'])) {
            return back()->withErrors(['project_id' => 'Pilih design catalog atau project, bukan kedua-duanya.']);
        }

        $project = ! empty($validated['project_id'])
            ? CustomerProject::query()
                ->whereKey($validated['project_id'])
                ->where('user_id', $order->user_id)
                ->first()
            : null;
        if (! empty($validated['project_id']) && ! $project) {
            return back()->withErrors(['project_id' => 'Project ini tidak dimiliki oleh customer order.']);
        }

        if ($validated['cut_type'] === 'die-cut' && ! empty($validated['size_id'])) {
            $size = StickerSize::query()->find($validated['size_id']);
            if ($size && max($size->width_cm, $size->height_cm) < 5) {
                return back()->withErrors(['size_id' => 'Potong ikut bentuk (die-cut) hanya boleh untuk saiz 5cm ke atas.']);
            }
        }

        $item->update([
            'sticker_design_id' => $validated['design_id'] ?? null,
            'customer_project_id' => $validated['project_id'] ?? null,
            'custom_design_description' => $project?->title ?? ($validated['custom_design_description'] ?? null),
            'sticker_size_id' => $validated['size_id'] ?? null,
            'requested_size' => $validated['requested_size'] ?? null,
            'quantity' => $validated['quantity'],
            'cut_type' => $validated['cut_type'],
            'line_total' => round((float) $item->unit_price * $validated['quantity'], 2),
        ]);

        $this->syncOrderTotals($order, $item, $shippingService);

        return back()->with('success', 'Item order berjaya dikemaskini.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_no' => ['required', 'string', 'max:50'],
        ]);

        $previousTrackingNo = trim((string) $order->tracking_no);
        $trackingNo = trim($validated['tracking_no']);

        $order->update([
            'status' => 'shipped',
            'tracking_no' => $trackingNo,
        ]);

        if ($trackingNo !== $previousTrackingNo) {
            $this->sendTrackingNotification($order);
        }

        return back()->with('success', 'No. tracking berjaya disimpan. Status order ditetapkan sebagai sedang dihantar.');
    }

    public function uploadItemFiles(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToOrder($order, $item);

        $validated = $request->validate([
            'source_file' => ['nullable', 'file', 'max:51200'],
            'preview_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'source_files' => ['nullable', 'array', 'max:20'],
            'source_files.*' => ['file', 'max:51200'],
            'preview_images' => ['nullable', 'array', 'max:20'],
            'preview_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ]);

        $sourceFiles = $request->file('source_files', []);
        $sourceFiles = is_array($sourceFiles) ? array_values(array_filter($sourceFiles)) : [$sourceFiles];
        if ($sourceFiles === [] && $request->hasFile('source_file')) {
            $sourceFiles = [$request->file('source_file')];
        }

        $previewImages = $request->file('preview_images', []);
        $previewImages = is_array($previewImages) ? array_values(array_filter($previewImages)) : [$previewImages];
        if ($previewImages === [] && $request->hasFile('preview_image')) {
            $previewImages = [$request->file('preview_image')];
        }

        if ($sourceFiles === [] && $previewImages === []) {
            return back()->withErrors(['source_file' => 'Pilih fail source atau gambar preview untuk item ini.']);
        }

        $updates = [];
        $sourcePaths = $this->sourcePaths($item);
        $previewPaths = $this->previewPaths($item);

        if ($sourceFiles !== []) {
            $sourcePaths = array_values(array_unique([
                ...$sourcePaths,
                ...collect($sourceFiles)
                    ->map(fn ($file): string => $file->store('order-items/sources', 'local'))
                    ->all(),
            ]));
            $updates['admin_source_path'] = $sourcePaths[0] ?? null;
            $updates['admin_source_paths'] = $sourcePaths ?: null;
        }

        if ($previewImages !== []) {
            try {
                $newPreviewPaths = collect($previewImages)
                    ->map(fn ($file): string => ImageOptimizer::store(
                        $file,
                        'order-items/previews/protected',
                        1000,
                        1000,
                        50,
                        'local',
                        'PREVIEW SAHAJA - BUKAN UNTUK CETAK - '.$order->order_no,
                    ))
                    ->all();
            } catch (\RuntimeException) {
                return back()->withErrors(['preview_images' => 'Gambar preview tidak dapat diproses. Sila guna format JPG, PNG, WEBP atau GIF.']);
            }

            $previewPaths = array_values(array_unique([
                ...$previewPaths,
                ...$newPreviewPaths,
            ]));
            $updates['customer_preview_path'] = $previewPaths[0] ?? null;
            $updates['customer_preview_paths'] = $previewPaths ?: null;
        }

        $item->update($updates);

        return back()->with('success', 'Fail item berjaya dimuat naik.');
    }

    public function deleteItemFile(Order $order, OrderItem $item, string $type, int $index): RedirectResponse
    {
        $this->ensureItemBelongsToOrder($order, $item);
        abort_unless(in_array($type, ['design', 'source', 'preview'], true), 404);

        $paths = $this->filePathsForType($item, $type);
        abort_unless(array_key_exists($index, $paths), 404);

        $path = $paths[$index];
        unset($paths[$index]);
        $item->update($this->filePathUpdates($type, array_values($paths)));
        $this->deleteStoredPathIfUnused($type, $path);

        return back()->with('success', 'Fail item berjaya dipadam.');
    }

    public function itemSource(Order $order, OrderItem $item, int $source = 0)
    {
        $this->ensureItemBelongsToOrder($order, $item);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $path = $this->sourcePaths($item)[$source] ?? null;
        abort_unless($path && $disk->exists($path), 404);

        return $disk->download($path, basename($path));
    }

    public function itemPreview(Order $order, OrderItem $item, int $preview = 0)
    {
        $this->ensureItemBelongsToOrder($order, $item);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $path = $this->previewPaths($item)[$preview] ?? null;
        abort_unless($path && $disk->exists($path), 404);

        $filePath = $disk->path($path);

        return response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath) ?: 'image/webp',
        ]);
    }

    public function itemPreviewDownload(Order $order, OrderItem $item, int $preview = 0)
    {
        $this->ensureItemBelongsToOrder($order, $item);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $path = $this->previewPaths($item)[$preview] ?? null;
        abort_unless($path && $disk->exists($path), 404);

        return $disk->download($path, 'preview-'.$item->id.'-'.($preview + 1).'.'.pathinfo($path, PATHINFO_EXTENSION));
    }

    public function quote(Request $request, Order $order, ShippingService $shippingService): RedirectResponse
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
        $shippingRegion = $shippingService->normalize($order->shipping_region);
        $shippingFee = $shippingService->calculate($amount, $shippingRegion);
        $total = round($amount + $shippingFee, 2);
        $deposit = min((float) (PaymentSetting::query()->value('deposit_amount') ?? 20), $total);

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
            'total' => $total,
            'shipping_region' => $shippingRegion,
            'shipping_fee' => $shippingFee,
            'deposit_amount' => $deposit,
            'balance_due' => max(0, $total - $deposit),
            'payment_status' => 'pending',
            'pricing_status' => 'awaiting_customer_approval',
            'price_note' => $validated['price_note'] ?? null,
            'price_quoted_at' => now(),
            'price_approved_at' => null,
        ]);

        return back()->with('success', 'Harga berjaya dihantar kepada customer untuk kelulusan.');
    }

    private function showProps(Order $order, bool $editMode, bool $itemEditEnabled): array
    {
        $order->load(['items.design', 'items.project', 'items.size', 'user', 'invoice']);
        $order->items->each(function (OrderItem $item): void {
            $item->setAttribute('files', $this->itemFiles($item->order_id, $item));
            $item->setAttribute('source_files', collect($this->sourcePaths($item))
                ->map(fn (string $path, int $index): array => [
                    'label' => 'Source '.($index + 1),
                    'url' => route('admin.orders.items.source', ['order' => $item->order_id, 'item' => $item->id, 'source' => $index]),
                ])
                ->values()
                ->all());
            $item->setAttribute('preview_files', collect($this->previewPaths($item))
                ->map(fn (string $path, int $index): array => [
                    'label' => 'Gambar '.($index + 1),
                    'url' => route('admin.orders.items.preview', ['order' => $item->order_id, 'item' => $item->id, 'preview' => $index]),
                ])
                ->values()
                ->all());
        });

        return [
            'order' => $order,
            'editMode' => $editMode,
            'itemEditEnabled' => $itemEditEnabled,
            'uploadedFiles' => $editMode ? [] : $this->uploadedFilesForOrder($order),
            'itemEditOptions' => $itemEditEnabled ? $this->itemEditOptions($order) : ['designs' => [], 'projects' => [], 'sizes' => []],
        ];
    }

    private function itemEditOptions(Order $order): array
    {
        $designIds = $order->items->pluck('sticker_design_id')->filter()->values();
        $sizeIds = $order->items->pluck('sticker_size_id')->filter()->values();

        return [
            'designs' => StickerDesign::query()
                ->where(function ($query) use ($designIds): void {
                    $query->where('is_active', true)->orWhereIn('id', $designIds);
                })
                ->orderBy('name')
                ->get(['id', 'name'])
                ->values()
                ->all(),
            'projects' => CustomerProject::query()
                ->where('user_id', $order->user_id)
                ->orderByDesc('updated_at')
                ->get(['id', 'title'])
                ->values()
                ->all(),
            'sizes' => StickerSize::query()
                ->where(function ($query) use ($sizeIds): void {
                    $query->where('is_active', true)->orWhereIn('id', $sizeIds);
                })
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->values()
                ->all(),
        ];
    }

    private function syncOrderTotals(Order $order, OrderItem $updatedItem, ShippingService $shippingService): void
    {
        $order->load(['items', 'invoice.items']);
        $subtotal = round($order->items->sum(fn (OrderItem $item): float => (float) $item->line_total), 2);
        $shippingFee = $order->shipping_region === null
            ? 0
            : $shippingService->calculate($subtotal, $order->shipping_region);
        $total = round($subtotal + $shippingFee, 2);
        $invoice = $order->invoice;
        $paid = (float) ($invoice?->total_paid ?? 0);

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'shipping_fee' => $shippingFee,
            'balance_due' => max(0, $total - ($invoice ? $paid : (float) $order->deposit_amount)),
        ]);

        if (! $invoice) {
            return;
        }

        $itemIndex = $order->items->search(fn (OrderItem $item): bool => $item->id === $updatedItem->id);
        $invoiceItem = $itemIndex === false ? null : $invoice->items->get($itemIndex);
        $updatedItem->load(['design', 'size']);

        if ($invoiceItem) {
            $invoiceItem->update([
                'description' => $this->invoiceItemDescription($updatedItem),
                'quantity' => $updatedItem->quantity,
                'line_total' => $updatedItem->line_total,
            ]);
        }

        if ($order->shipping_region !== null) {
            $shippingItem = $invoice->items->first(
                fn ($item): bool => str_starts_with((string) $item->description, 'Pos - '),
            );
            $shippingDescription = $shippingService->description($order->shipping_region, $shippingFee);

            if ($shippingItem) {
                $shippingItem->update([
                    'description' => $shippingDescription,
                    'unit_price' => $shippingFee,
                    'line_total' => $shippingFee,
                ]);
            } else {
                $invoice->items()->create([
                    'description' => $shippingDescription,
                    'quantity' => 1,
                    'unit_price' => $shippingFee,
                    'line_total' => $shippingFee,
                ]);
            }
        }

        $invoice->update(['amount' => $total]);
    }

    private function invoiceItemDescription(OrderItem $item): string
    {
        return collect([
            $item->design?->name,
            $item->custom_design_description,
            $item->size?->name,
            $item->requested_size ? "Saiz: {$item->requested_size}" : null,
            $item->cut_type === 'die-cut' ? 'Potong Ikut Bentuk' : 'Potong Standard',
        ])->filter()->implode(' • ') ?: 'Sticker';
    }

    private function itemFiles(int $orderId, OrderItem $item): array
    {
        $designFiles = collect($this->designPaths($item))
            ->map(function (string $path, int $index) use ($item): array {
                $url = url('storage/'.$path);
                $isImage = $this->isImagePath($path);

                return [
                    'id' => 'design-'.$item->id.'-'.$index,
                    'type' => 'design',
                    'index' => $index,
                    'label' => 'Design '.($index + 1),
                    'name' => basename($path),
                    'url' => $url,
                    'download_url' => $url,
                    'preview_url' => $isImage ? $url : null,
                    'is_image' => $isImage,
                    'origin' => 'create_order',
                    'origin_label' => 'Upload customer',
                    'file_type_label' => 'Fail design customer',
                ];
            });

        $sourceFiles = collect($this->sourcePaths($item))
            ->map(function (string $path, int $index) use ($orderId, $item): array {
                $url = route('admin.orders.items.source', [
                    'order' => $orderId,
                    'item' => $item->id,
                    'source' => $index,
                ]);

                return [
                    'id' => 'source-'.$item->id.'-'.$index,
                    'type' => 'source',
                    'index' => $index,
                    'label' => 'Source '.($index + 1),
                    'name' => basename($path),
                    'url' => $url,
                    'download_url' => $url,
                    'preview_url' => null,
                    'is_image' => false,
                    'origin' => 'admin',
                    'origin_label' => 'Upload oleh admin',
                    'file_type_label' => 'Fail source admin',
                ];
            });

        $previewFiles = collect($this->previewPaths($item))
            ->map(function (string $path, int $index) use ($orderId, $item): array {
                $url = route('admin.orders.items.preview', [
                    'order' => $orderId,
                    'item' => $item->id,
                    'preview' => $index,
                ]);

                return [
                    'id' => 'preview-'.$item->id.'-'.$index,
                    'type' => 'preview',
                    'index' => $index,
                    'label' => 'Gambar '.($index + 1),
                    'name' => basename($path),
                    'url' => $url,
                    'download_url' => route('admin.orders.items.preview-download', [
                        'order' => $orderId,
                        'item' => $item->id,
                        'preview' => $index,
                    ]),
                    'preview_url' => $url,
                    'is_image' => true,
                    'origin' => 'admin',
                    'origin_label' => 'Upload oleh admin',
                    'file_type_label' => 'Gambar preview customer',
                ];
            });

        return $designFiles
            ->merge($sourceFiles)
            ->merge($previewFiles)
            ->values()
            ->all();
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
                            'origin' => 'create_order',
                            'origin_label' => 'Upload masa create order',
                            'file_type_label' => 'Fail design customer',
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
                        'origin' => 'create_order',
                        'origin_label' => 'Upload masa create order',
                        'file_type_label' => 'Fail project',
                    ];
                })->all();
            });

        $adminSourceFiles = $order->items
            ->values()
            ->flatMap(function (OrderItem $item, int $itemIndex): array {
                return collect($this->sourcePaths($item))
                    ->map(function (string $path, int $fileIndex) use ($item, $itemIndex): array {
                        $url = route('admin.orders.items.source', [
                            'order' => $item->order_id,
                            'item' => $item->id,
                            'source' => $fileIndex,
                        ]);

                        return [
                            'id' => 'admin-source-'.$item->id.'-'.$fileIndex,
                            'item_label' => $this->itemReference($item, $itemIndex),
                            'name' => basename($path),
                            'url' => $url,
                            'download_url' => $url,
                            'preview_url' => null,
                            'is_image' => false,
                            'origin' => 'admin',
                            'origin_label' => 'Upload oleh admin',
                            'file_type_label' => 'Fail source admin',
                        ];
                    })
                    ->all();
            });

        $previewFiles = $order->items
            ->values()
            ->flatMap(function (OrderItem $item, int $itemIndex) use ($order): array {
                return collect($this->previewPaths($item))
                    ->map(function (string $path, int $previewIndex) use ($order, $item, $itemIndex): array {
                        $previewUrl = route('admin.orders.items.preview', ['order' => $order, 'item' => $item, 'preview' => $previewIndex]);

                        return [
                            'id' => 'item-preview-'.$item->id.'-'.$previewIndex,
                            'item_label' => $this->itemReference($item, $itemIndex),
                            'name' => 'Gambar preview design',
                            'url' => $previewUrl,
                            'download_url' => route('admin.orders.items.preview-download', ['order' => $order, 'item' => $item, 'preview' => $previewIndex]),
                            'preview_url' => $previewUrl,
                            'is_image' => true,
                            'origin' => 'admin',
                            'origin_label' => 'Upload oleh admin',
                            'file_type_label' => 'Gambar preview customer',
                        ];
                    })
                    ->all();
            });

        return $designFiles
            ->merge($projectFiles)
            ->merge($adminSourceFiles)
            ->merge($previewFiles)
            ->values()
            ->all();
    }

    private function ensureItemBelongsToOrder(Order $order, OrderItem $item): void
    {
        abort_unless((int) $item->order_id === (int) $order->id, 404);
    }

    private function sourcePaths(OrderItem $item): array
    {
        return collect($item->admin_source_paths ?: [$item->admin_source_path])
            ->filter()
            ->values()
            ->all();
    }

    private function designPaths(OrderItem $item): array
    {
        return collect($item->customer_design_paths ?: [$item->customer_design_path])
            ->filter()
            ->values()
            ->all();
    }

    private function previewPaths(OrderItem $item): array
    {
        return collect($item->customer_preview_paths ?: [$item->customer_preview_path])
            ->filter()
            ->values()
            ->all();
    }

    private function filePathsForType(OrderItem $item, string $type): array
    {
        return match ($type) {
            'design' => $this->designPaths($item),
            'source' => $this->sourcePaths($item),
            'preview' => $this->previewPaths($item),
            default => abort(404),
        };
    }

    private function filePathUpdates(string $type, array $paths): array
    {
        $paths = array_values($paths);

        return match ($type) {
            'design' => [
                'customer_design_path' => $paths[0] ?? null,
                'customer_design_paths' => $paths ?: null,
            ],
            'source' => [
                'admin_source_path' => $paths[0] ?? null,
                'admin_source_paths' => $paths ?: null,
            ],
            'preview' => [
                'customer_preview_path' => $paths[0] ?? null,
                'customer_preview_paths' => $paths ?: null,
            ],
            default => abort(404),
        };
    }

    private function deleteStoredPathIfUnused(string $type, string $path): void
    {
        $isReferenced = OrderItem::query()
            ->get([
                'customer_design_path',
                'customer_design_paths',
                'admin_source_path',
                'admin_source_paths',
                'customer_preview_path',
                'customer_preview_paths',
            ])
            ->contains(fn (OrderItem $item): bool => in_array($path, $this->filePathsForType($item, $type), true));

        if ($isReferenced) {
            return;
        }

        Storage::disk($type === 'design' ? 'public' : 'local')->delete($path);
    }

    private function isImagePath(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
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
            ."Status: shipped\n\n"
            .'Semak status order: '.route('orders.lookup-form');

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
                'status' => 'shipped',
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
