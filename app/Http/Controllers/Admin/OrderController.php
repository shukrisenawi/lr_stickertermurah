<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();

        $orders = Order::query()
            ->with(['user', 'invoice'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', $this->showProps($order));
    }

    public function update(Request $request, Order $order): Response
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,processing,shipped,completed,cancelled'],
            'tracking_no' => ['nullable', 'string', 'max:50'],
        ]);

        $order->update($validated);

        return Inertia::render('Admin/Orders/Show', $this->showProps($order))
            ->with('success', 'Order berjaya dikemaskini.');
    }

    public function quote(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'price_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($order->invoice) {
            return back()->with('error', 'Harga tidak boleh diubah selepas invoice dicipta.');
        }

        $order->loadMissing('items');
        $item = $order->items->first();

        if (! $item) {
            return back()->with('error', 'Order ini tiada item untuk ditetapkan harga.');
        }

        $amount = round((float) $validated['amount'], 2);
        $deposit = min((float) (PaymentSetting::query()->value('deposit_amount') ?? 20), $amount);

        $item->update([
            'unit_price' => round($amount / max(1, $item->quantity), 2),
            'line_total' => $amount,
        ]);

        $order->update([
            'subtotal' => $amount,
            'total' => $amount,
            'deposit_amount' => $deposit,
            'balance_due' => max(0, $amount - $deposit),
            'payment_status' => 'pending',
            'pricing_status' => 'awaiting_customer_approval',
            'price_note' => $validated['price_note'] ?? null,
            'price_quoted_at' => now(),
            'price_approved_at' => null,
        ]);

        return back()->with('success', 'Harga berjaya dihantar kepada customer untuk kelulusan.');
    }

    private function showProps(Order $order): array
    {
        return [
            'order' => $order->load(['items.design', 'items.project', 'items.size', 'user', 'invoice']),
            'customerProjects' => $this->customerProjectsForOrder($order),
        ];
    }

    private function customerProjectsForOrder(Order $order): array
    {
        if (! $order->user_id) {
            return [];
        }

        return CustomerProject::query()
            ->where('user_id', $order->user_id)
            ->with('order')
            ->latest()
            ->get()
            ->map(function (CustomerProject $project): array {
                $sourcePaths = collect($project->source_paths ?: [$project->source_path])
                    ->filter()
                    ->values();

                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'preview_url' => $project->preview_path ? route('admin.projects.preview', $project) : null,
                    'source_files' => $sourcePaths
                        ->map(fn (string $path, int $index) => [
                            'name' => basename($path),
                            'url' => route('admin.projects.source', ['project' => $project, 'source' => $index]),
                        ])
                        ->all(),
                    'created_at' => $project->created_at,
                    'order_no' => $project->order?->order_no,
                ];
            })
            ->values()
            ->all();
    }
}
