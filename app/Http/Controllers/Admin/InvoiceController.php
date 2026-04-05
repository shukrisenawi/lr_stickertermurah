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

    public function createManual(): View
    {
        $orders = Order::query()
            ->whereDoesntHave('invoice')
            ->with('user')
            ->latest()
            ->limit(500)
            ->get();

        return view('admin.invoices.manual', [
            'orders' => $orders,
        ]);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'invoice_no' => ['nullable', 'string', 'max:255', 'unique:invoices,invoice_no'],
            'issue_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = Order::query()->with('invoice')->findOrFail((int) $validated['order_id']);

        if ($order->invoice) {
            return back()->withInput()->with('error', 'Invoice untuk order ini sudah wujud.');
        }

        Invoice::query()->create([
            'order_id' => $order->id,
            'invoice_no' => $validated['invoice_no'] ?: $this->generateInvoiceNo(),
            'issue_date' => $validated['issue_date'],
            'amount' => (float) $validated['amount'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.invoices.create')->with('success', 'Invoice manual berjaya dicipta.');
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
            'invoice_no' => $this->generateInvoiceNo(),
            'issue_date' => now()->toDateString(),
            'amount' => $order->total,
            'notes' => $notes,
        ]);
    }

    private function generateInvoiceNo(): string
    {
        do {
            $invoiceNo = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (Invoice::query()->where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }
}
