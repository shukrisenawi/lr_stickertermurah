<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function show(Invoice $invoice): Response
    {
        $invoice->load('order.items.design', 'order.items.size');

        abort_if($invoice->order->user_id !== Auth::id(), 403);

        return Inertia::render('Member/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }
}
