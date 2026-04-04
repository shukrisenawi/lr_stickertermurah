<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        if ($order->invoice) {
            return back()->with('error', 'Invoice untuk order ini sudah wujud.');
        }

        Invoice::query()->create([
            'order_id' => $order->id,
            'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
            'issue_date' => now()->toDateString(),
            'amount' => $order->total,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Invoice berjaya dicipta.');
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load('order.items.design', 'order.items.size'),
        ]);
    }
}
