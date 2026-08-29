<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Services\InvoiceService;
use App\Services\ShippingService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->with(['items.design', 'items.size', 'invoice'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Member/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorizeOrder($order);

        $order = $order->load(['items.design', 'items.project', 'items.size', 'invoice']);
        $order->items->each(function (OrderItem $item) use ($order): void {
            $previewUrls = collect($this->previewPaths($item))
                ->map(fn (string $path, int $index): string => route('member.orders.items.preview', [
                    'order' => $order,
                    'item' => $item,
                    'preview' => $index,
                ]))
                ->values()
                ->all();
            $item->setAttribute('preview_urls', $previewUrls);
            $item->setAttribute('preview_url', $previewUrls[0] ?? null);
        });

        $itemEditOptions = [
            'designs' => [],
            'projects' => [],
            'sizes' => [],
        ];

        if ($order->status === 'pending') {
            $designIds = $order->items->pluck('sticker_design_id')->filter()->values();
            $sizeIds = $order->items->pluck('sticker_size_id')->filter()->values();

            $itemEditOptions = [
                'designs' => StickerDesign::query()
                    ->where(function ($query) use ($designIds): void {
                        $query->where('is_active', true)->orWhereIn('id', $designIds);
                    })
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->values()
                    ->all(),
                'projects' => CustomerProject::query()
                    ->where('user_id', Auth::id())
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

        return Inertia::render('Member/Orders/Show', [
            'order' => $order,
            'itemEditOptions' => $itemEditOptions,
        ]);
    }

    public function updateItem(Request $request, Order $order, OrderItem $item, ShippingService $shippingService): RedirectResponse
    {
        $this->authorizeOrder($order);
        abort_unless((int) $item->order_id === (int) $order->id, 404);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Item hanya boleh dikemaskini ketika order menunggu semakan.');
        }

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
            return back()->withErrors(['project_id' => 'Project ini bukan milik anda.']);
        }

        if ($validated['cut_type'] === 'die-cut' && ! empty($validated['size_id'])) {
            $size = StickerSize::query()->find($validated['size_id']);
            if ($size && max($size->width_cm, $size->height_cm) < 5) {
                return back()->withErrors(['size_id' => 'Potong ikut bentuk (die-cut) hanya boleh untuk saiz 5cm ke atas.']);
            }
        }

        $keepsQuotedPricing = $item->quoted_qty_per_a3
            && $item->quoted_price_per_a3
            && (int) $item->sticker_size_id === (int) ($validated['size_id'] ?? 0)
            && trim((string) $item->requested_size) === trim((string) ($validated['requested_size'] ?? ''));
        $lineTotal = $keepsQuotedPricing
            ? round(ceil($validated['quantity'] / $item->quoted_qty_per_a3) * (float) $item->quoted_price_per_a3, 2)
            : round((float) $item->unit_price * $validated['quantity'], 2);

        $item->update([
            'sticker_design_id' => $validated['design_id'] ?? null,
            'customer_project_id' => $validated['project_id'] ?? null,
            'custom_design_description' => $project?->title ?? ($validated['custom_design_description'] ?? null),
            'sticker_size_id' => $validated['size_id'] ?? null,
            'requested_size' => $validated['requested_size'] ?? null,
            'quantity' => $validated['quantity'],
            'cut_type' => $validated['cut_type'],
            'unit_price' => round($lineTotal / max(1, $validated['quantity']), 2),
            'line_total' => $lineTotal,
            'quoted_qty_per_a3' => $keepsQuotedPricing ? $item->quoted_qty_per_a3 : null,
            'quoted_price_per_a3' => $keepsQuotedPricing ? $item->quoted_price_per_a3 : null,
            'quoted_sticker_type' => $keepsQuotedPricing ? $item->quoted_sticker_type : null,
        ]);

        $this->syncOrderTotals($order, $item, $shippingService);

        return back()->with('success', 'Item order berjaya dikemaskini.');
    }

    public function itemPreview(Order $order, OrderItem $item, int $preview = 0)
    {
        $this->authorizeOrder($order);
        abort_unless((int) $item->order_id === (int) $order->id, 404);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $path = $this->previewPaths($item)[$preview] ?? null;
        abort_unless($path && $disk->exists($path), 404);

        $filePath = $disk->path($path);

        $response = response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath) ?: 'image/webp',
        ]);
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function repeat(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        return redirect()->route('member.orders.repeat-form', array_filter([
            'repeatOrder' => $order->id,
            'project_id' => $request->integer('project_id') ?: null,
        ]));
    }

    public function approvePrice(Order $order, InvoiceService $invoiceService): RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->pricing_status !== 'awaiting_customer_approval') {
            return back()->with('error', 'Harga order ini belum menunggu kelulusan anda.');
        }

        $order->update([
            'pricing_status' => 'approved',
            'price_approved_at' => now(),
        ]);

        $invoice = $invoiceService->createForOrder($order->refresh());

        return redirect()
            ->route('member.invoices.show', ['invoice' => $invoice, 'pay' => 1])
            ->with('success', 'Harga berjaya diluluskan. Invoice telah dicipta dan sedia untuk bayaran.');
    }

    private function authorizeOrder(Order $order): void
    {
        abort_if($order->user_id !== Auth::id(), 403);
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
                fn ($invoiceItem): bool => str_starts_with((string) $invoiceItem->description, 'Pos - '),
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
            $item->quoted_sticker_type ? "Jenis: {$item->quoted_sticker_type}" : null,
            $item->quoted_qty_per_a3 && $item->quoted_price_per_a3
                ? "Kiraan: {$item->quoted_qty_per_a3} pcs/A3 @ RM".number_format((float) $item->quoted_price_per_a3, 2).'/A3'
                : null,
            $item->cut_type === 'die-cut' ? 'Potong Ikut Bentuk' : 'Potong Standard',
        ])->filter()->implode(' • ') ?: 'Sticker';
    }

    private function previewPaths(OrderItem $item): array
    {
        return collect($item->customer_preview_paths ?: [$item->customer_preview_path])
            ->filter()
            ->values()
            ->all();
    }
}
