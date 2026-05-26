<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

        return Inertia::render('Member/Orders/Show', [
            'order' => $order->load(['items.design', 'items.size', 'invoice']),
        ]);
    }

    public function repeat(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        return redirect()->route('orders.repeat', ['repeatOrder' => $order->id]);
    }

    private function authorizeOrder(Order $order): void
    {
        abort_if($order->user_id !== Auth::id(), 403);
    }
}
