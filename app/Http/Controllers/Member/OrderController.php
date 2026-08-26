<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InvoiceService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        return Inertia::render('Member/Orders/Show', [
            'order' => $order,
        ]);
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

    private function previewPaths(OrderItem $item): array
    {
        return collect($item->customer_preview_paths ?: [$item->customer_preview_path])
            ->filter()
            ->values()
            ->all();
    }
}
