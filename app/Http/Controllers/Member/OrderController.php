<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->with(['items.design', 'items.size', 'invoice'])
            ->latest()
            ->paginate(10);

        return view('member.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorizeOrder($order);

        return view('member.orders.show', [
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
