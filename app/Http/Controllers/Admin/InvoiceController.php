<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function create(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $orders = Order::query()
            ->whereDoesntHave('invoice')
            ->with('user')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_no', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%')
                        ->orWhere('customer_phone', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.create', [
            'orders' => $orders,
            'search' => $search,
        ]);
    }

    public function storeFromMenu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = Order::query()->with('invoice')->findOrFail((int) $validated['order_id']);

        if ($order->invoice) {
            return back()->with('error', 'Invoice untuk order ini sudah wujud.');
        }

        $this->createInvoiceForOrder($order, $validated['notes'] ?? null);

        return back()->with('success', 'Invoice berjaya dicipta.');
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        if ($order->invoice) {
            return back()->with('error', 'Invoice untuk order ini sudah wujud.');
        }

        $this->createInvoiceForOrder($order, $validated['notes'] ?? null);

        return back()->with('success', 'Invoice berjaya dicipta.');
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load('order.items.design', 'order.items.size'),
        ]);
    }

    private function createInvoiceForOrder(Order $order, ?string $notes): void
    {
        Invoice::query()->create([
            'order_id' => $order->id,
            'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
            'issue_date' => now()->toDateString(),
            'amount' => $order->total,
            'notes' => $notes,
        ]);
    }
}
