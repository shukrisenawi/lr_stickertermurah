<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
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
            $item->setAttribute(
                'preview_url',
                $item->customer_preview_path
                    ? route('member.orders.items.preview', ['order' => $order, 'item' => $item])
                    : null,
            );
        });

        return Inertia::render('Member/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function itemPreview(Order $order, OrderItem $item)
    {
        $this->authorizeOrder($order);
        abort_unless((int) $item->order_id === (int) $order->id, 404);
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($item->customer_preview_path && $disk->exists($item->customer_preview_path), 404);

        $path = $disk->path($item->customer_preview_path);

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'image/webp',
        ]);
    }

    public function repeat(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        return redirect()->route('member.orders.repeat-form', array_filter([
            'repeatOrder' => $order->id,
            'project_id' => $request->integer('project_id') ?: null,
        ]));
    }

    public function approvePrice(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->pricing_status !== 'awaiting_customer_approval') {
            return back()->with('error', 'Harga order ini belum menunggu kelulusan anda.');
        }

        $order->update([
            'pricing_status' => 'approved',
            'price_approved_at' => now(),
        ]);

        return back()->with('success', 'Harga berjaya diluluskan. Admin kini boleh mencipta invoice untuk order ini.');
    }

    private function authorizeOrder(Order $order): void
    {
        abort_if($order->user_id !== Auth::id(), 403);
    }
}
