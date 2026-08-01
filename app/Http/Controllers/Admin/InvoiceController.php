<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function create(Request $request): Response
    {
        $search = trim($request->string('q')->toString());

        $orders = Order::query()
            ->whereDoesntHave('invoice')
            ->with('user')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_no', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Invoices/Create', [
            'orders' => $orders,
            'search' => $search,
        ]);
    }

    public function createManual(): Response
    {
        $customers = User::query()
            ->where('is_admin', false)
            ->with(['customerAddresses' => function ($q) {
                $q->orderByDesc('is_default')->orderByDesc('updated_at');
            }])
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'addresses' => $user->customerAddresses->map(fn ($a) => [
                        'id' => $a->id,
                        'address' => $a->address,
                        'no_hp' => $a->no_hp,
                        'is_default' => $a->is_default,
                    ]),
                ];
            });

        return Inertia::render('Admin/Invoices/ManualCreate', [
            'customers' => $customers,
        ]);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_address' => ['required', 'string'],
            'invoice_no' => ['nullable', 'string', 'max:255', 'unique:invoices,invoice_no'],
            'issue_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $amount = (float) $validated['amount'];
        $calculatedTotal = collect($validated['items'])
            ->sum(fn (array $item): float => (int) $item['quantity'] * (float) $item['unit_price']);

        if (abs($calculatedTotal - $amount) > 0.01) {
            return back()->withInput()->with('error', 'Jumlah invoice tidak sama dengan jumlah item. Jumlah sepatutnya RM '.number_format($calculatedTotal, 2));
        }

        $invoice = Invoice::query()->create([
            'user_id' => $validated['user_id'] ?? null,
            'invoice_no' => $validated['invoice_no'] ?: $this->generateInvoiceNo(),
            'issue_date' => $validated['issue_date'],
            'amount' => $amount,
            'notes' => $validated['notes'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
        ]);

        foreach ($validated['items'] as $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $quantity * $unitPrice,
            ]);
        }

        return redirect()->route('admin.invoices.show', $invoice->id)->with('success', 'Invoice manual berjaya dicipta.');
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

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['items', 'order.items.design', 'order.items.size', 'user', 'approver']);

        $receiptUrl = $invoice->payment_receipt_path
            ? Storage::disk('public')->url($invoice->payment_receipt_path)
            : null;

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice,
            'receiptUrl' => $receiptUrl,
        ]);
    }

    private function createInvoiceForOrder(Order $order, ?string $notes): void
    {
        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'invoice_no' => $this->generateInvoiceNo(),
            'issue_date' => now()->toDateString(),
            'amount' => $order->total,
            'notes' => $notes,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
        ]);

        // Cipta InvoiceItem rows dari OrderItem supaya invoice papar item dengan betul
        $order->loadMissing('items.design', 'items.size');

        foreach ($order->items as $item) {
            $description = collect([
                $item->design?->name,
                $item->custom_design_description,
                $item->size?->name,
                $item->requested_size ? "Saiz: {$item->requested_size}" : null,
                $item->cut_type === 'die-cut' ? 'Potong Ikut Bentuk' : 'Potong Standard',
            ])->filter()->implode(' • ');

            $invoice->items()->create([
                'description' => $description ?: 'Sticker',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ]);
        }
    }

    private function generateInvoiceNo(): string
    {
        do {
            $invoiceNo = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (Invoice::query()->where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }
}
