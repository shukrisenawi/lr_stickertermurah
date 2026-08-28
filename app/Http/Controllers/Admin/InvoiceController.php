<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use App\Support\CustomerNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('payment_status')->toString();

        $invoices = Invoice::query()
            ->with(['user', 'order', 'approver'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('invoice_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhereHas('order', function (Builder $orderQuery) use ($search): void {
                            $orderQuery->where('order_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function (Builder $query) use ($status): void {
                match ($status) {
                    'unpaid' => $query->whereIn('payment_status', ['unpaid', 'submitted', 'rejected']),
                    'partial' => $query->where('payment_status', 'partial'),
                    'paid' => $query->where('payment_status', 'paid'),
                    default => $query->where('payment_status', $status),
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => Invoice::query()->count(),
            'unpaid' => Invoice::query()->whereIn('payment_status', ['unpaid', 'submitted', 'rejected'])->count(),
            'partial' => Invoice::query()->where('payment_status', 'partial')->count(),
            'paid' => Invoice::query()->where('payment_status', 'paid')->count(),
        ];

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
            'counts' => $counts,
            'filters' => [
                'search' => $search,
                'payment_status' => $status,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $search = trim($request->string('q')->toString());

        $orders = Order::query()
            ->whereDoesntHave('invoice')
            ->where('total', '>', 0)
            ->where(function (Builder $query): void {
                $query->whereIn('pricing_status', ['auto_priced', 'approved'])
                    ->orWhere(function (Builder $legacyQuery): void {
                        $legacyQuery->where(function (Builder $statusQuery): void {
                            $statusQuery->whereNull('pricing_status')->orWhere('pricing_status', 'pending');
                        })->where('total', '>', 0);
                    });
            })
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

    public function createManual(Request $request): Response
    {
        $initialUserId = $request->integer('user_id');
        if ($initialUserId < 1 || ! User::query()->whereKey($initialUserId)->where('is_admin', false)->exists()) {
            $initialUserId = null;
        }

        $initialAddressId = $request->integer('address_id');
        if ($initialUserId === null || $initialAddressId < 1 || ! CustomerAddress::query()->whereKey($initialAddressId)->where('user_id', $initialUserId)->exists()) {
            $initialAddressId = null;
        }

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
                        'recipient_name' => $a->recipient_name,
                        'address' => $a->address,
                        'no_hp' => $a->no_hp,
                        'is_default' => $a->is_default,
                    ]),
                ];
            });

        return Inertia::render('Admin/Invoices/ManualCreate', [
            'customers' => $customers,
            'initialUserId' => $initialUserId,
            'initialAddressId' => $initialAddressId,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $invoice->load(['items', 'order', 'user']);

        return Inertia::render('Admin/Invoices/Edit', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'issue_date' => $invoice->issue_date?->format('Y-m-d'),
                'notes' => $invoice->notes,
                'customer_name' => $invoice->customer_name ?? $invoice->order?->customer_name ?? $invoice->user?->name ?? '',
                'customer_phone' => $invoice->customer_phone ?? $invoice->order?->customer_phone ?? '',
                'customer_address' => $invoice->customer_address ?? $invoice->order?->customer_address ?? '',
                'amount' => (float) $invoice->amount,
                'total_paid' => (float) $invoice->total_paid,
                'items' => $invoice->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                ])->values(),
            ],
        ]);
    }

    public function update(Request $request, Invoice $invoice, InvoiceService $invoiceService): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_address' => ['required', 'string'],
            'invoice_no' => ['required', 'string', 'max:255', Rule::unique('invoices', 'invoice_no')->ignore($invoice->id)],
            'issue_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $calculatedTotal = collect($validated['items'])
            ->sum(fn (array $item): float => (int) $item['quantity'] * (float) $item['unit_price']);
        $totalPaid = (float) $invoice->total_paid;

        if ($calculatedTotal + 0.01 < $totalPaid) {
            return back()->withInput()->withErrors([
                'items' => 'Jumlah invoice tidak boleh kurang daripada jumlah bayaran yang telah diterima (RM '.number_format($totalPaid, 2).').',
            ]);
        }

        $invoice->update([
            'invoice_no' => $validated['invoice_no'],
            'issue_date' => $validated['issue_date'],
            'amount' => $calculatedTotal,
            'notes' => $validated['notes'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
        ]);

        $invoice->items()->delete();

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

        if ($invoice->user_id) {
            $invoiceService->syncCustomerAddress(
                (int) $invoice->user_id,
                $validated['customer_address'],
                $validated['customer_phone'],
            );
        }

        $this->notifyCustomerInvoiceUpdate(
            $invoice,
            'Invoice dikemaskini',
            "Invoice {$invoice->invoice_no} telah dikemaskini oleh admin.",
        );

        return redirect()->route('admin.invoices.show', $invoice->id)->with('success', 'Invoice berjaya dikemaskini.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $receiptPaths = $invoice->payments()
            ->pluck('receipt_path')
            ->push($invoice->payment_receipt_path)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $invoice->delete();

        if ($receiptPaths !== []) {
            Storage::disk('public')->delete($receiptPaths);
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice berjaya dipadam.');
    }

    public function storeManual(Request $request, InvoiceService $invoiceService): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
            'customer_address_id' => [
                'nullable',
                'integer',
                Rule::exists('customer_addresses', 'id')->where(fn ($query) => $query->where('user_id', $request->input('user_id'))),
            ],
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

        $customerAddress = null;
        if (! empty($validated['customer_address_id'])) {
            $customerAddress = CustomerAddress::query()
                ->whereKey($validated['customer_address_id'])
                ->where('user_id', $validated['user_id'])
                ->firstOrFail();
            $validated['customer_name'] = $customerAddress->recipient_name ?: $validated['customer_name'];
            $validated['customer_phone'] = $customerAddress->no_hp ?: $validated['customer_phone'];
            $validated['customer_address'] = $customerAddress->address;
        }

        $invoice = Invoice::query()->create([
            'user_id' => $validated['user_id'] ?? null,
            'customer_address_id' => $customerAddress?->id,
            'invoice_no' => $validated['invoice_no'] ?? $invoiceService->generateInvoiceNo(),
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

        // Auto-save alamat ke user jika ada user_id & alamat berbeza dari sedia ada
        if (! empty($validated['user_id'])) {
            $invoiceService->syncCustomerAddress(
                (int) $validated['user_id'],
                $validated['customer_address'],
                $validated['customer_phone'],
            );
        }

        $this->notifyCustomerInvoiceUpdate(
            $invoice,
            'Invoice baharu tersedia',
            "Invoice {$invoice->invoice_no} telah disediakan oleh admin.",
        );

        return redirect()->route('admin.invoices.show', $invoice->id)->with('success', 'Invoice manual berjaya dicipta.');
    }

    public function storeFromMenu(Request $request, InvoiceService $invoiceService): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = Order::query()->with('invoice')->findOrFail((int) $validated['order_id']);

        if ($order->invoice) {
            return back()->with('error', 'Invoice untuk order ini sudah wujud.');
        }

        if ((float) $order->total <= 0 || in_array($order->pricing_status, ['pending_admin', 'awaiting_customer_approval'], true)) {
            return back()->with('error', 'Order ini belum mempunyai harga yang diluluskan customer.');
        }

        $invoice = $invoiceService->createForOrder($order, $validated['notes'] ?? null);
        $this->notifyCustomerInvoiceUpdate(
            $invoice,
            'Invoice baharu tersedia',
            "Invoice {$invoice->invoice_no} telah disediakan oleh admin.",
        );

        return back()->with('success', 'Invoice berjaya dicipta.');
    }

    public function store(Request $request, Order $order, InvoiceService $invoiceService): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        if ($order->invoice) {
            return back()->with('error', 'Invoice untuk order ini sudah wujud.');
        }

        if ((float) $order->total <= 0 || in_array($order->pricing_status, ['pending_admin', 'awaiting_customer_approval'], true)) {
            return back()->with('error', 'Order ini belum mempunyai harga yang diluluskan customer.');
        }

        $invoice = $invoiceService->createForOrder($order, $validated['notes'] ?? null);
        $this->notifyCustomerInvoiceUpdate(
            $invoice,
            'Invoice baharu tersedia',
            "Invoice {$invoice->invoice_no} telah disediakan oleh admin.",
        );

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
            'customerInvoiceUrl' => URL::temporarySignedRoute(
                'invoices.public',
                now()->addDays(7),
                ['invoice' => $invoice],
            ),
            'totalPaid' => (float) $invoice->total_paid,
            'balanceDue' => $invoice->balanceDue(),
        ]);
    }

    public function updateTracking(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_no' => ['nullable', 'string', 'max:50'],
        ]);

        $previousTrackingNo = trim((string) $invoice->tracking_no);
        $trackingNo = trim($validated['tracking_no'] ?? '');
        $invoice->update([
            'tracking_no' => $trackingNo ?: null,
        ]);

        if ($trackingNo !== $previousTrackingNo) {
            $message = $trackingNo !== ''
                ? "No. tracking invoice {$invoice->invoice_no} telah dikemaskini: {$trackingNo}."
                : "No. tracking invoice {$invoice->invoice_no} telah dikosongkan oleh admin.";
            $this->notifyCustomerInvoiceUpdate($invoice, 'Tracking invoice dikemaskini', $message, 'tracking');
        }

        return back()->with('success', 'No. tracking J&T berjaya dikemaskini.');
    }

    private function notifyCustomerInvoiceUpdate(
        Invoice $invoice,
        string $title,
        string $message,
        string $type = 'invoice',
    ): void {
        CustomerNotifier::forInvoice(
            $invoice,
            $title,
            $message,
            route('member.invoices.show', $invoice),
            $type,
        );
    }
}
