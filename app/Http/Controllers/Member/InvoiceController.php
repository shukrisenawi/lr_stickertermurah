<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Invoice $invoice): View
    {
        $invoice->load('order.items.design', 'order.items.size');

        abort_if($invoice->order->user_id !== Auth::id(), 403);

        return view('member.invoices.show', [
            'invoice' => $invoice,
        ]);
    }
}
