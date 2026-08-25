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
        $status = $request->string('status')->toString() === 'completed' ? 'completed' : 'pending';

        $orders = Order::query()
            ->with(['user', 'invoice'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($status === 'completed', function ($query) {
                $query->where('status', 'completed');
            })
            ->when($status === 'pending', function ($query) {
                $query->where('status', '!=', 'completed');
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
        return Inertia::render('Admin/Orders/Show', $this->showProps($order, false));
    }

    public function edit(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', $this->showProps($order, true));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order berjaya dipadam.');
    }

    public function update(Request $request, Order $order): Response
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,processing,shipped,completed,cancelled'],
            'tracking_no' => ['nullable', 'string', 'max:50'],
        ]);

        $trackingNo = trim((string) ($validated['tracking_no'] ?? ''));

        $order->update([
            'status' => $trackingNo !== '' ? 'completed' : $validated['status'],
            'tracking_no' => $trackingNo !== '' ? $trackingNo : null,
        ]);

        return Inertia::render('Admin/Orders/Show', $this->showProps($order, false))
            ->with('success', 'Order berjaya dikemaskini.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_no' => ['required', 'string', 'max:50'],
        ]);

        $order->update([
            'status' => 'completed',
            'tracking_no' => trim($validated['tracking_no']),
        ]);

        return back()->with('success', 'No. tracking berjaya disimpan. Status order ditetapkan sebagai completed.');
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
        $items = $order->items->values();

        if ($items->isEmpty()) {
            return back()->with('error', 'Order ini tiada item untuk ditetapkan harga.');
        }

        $amount = round((float) $validated['amount'], 2);
        $deposit = min((float) (PaymentSetting::query()->value('deposit_amount') ?? 20), $amount);

        $weights = $items->map(fn ($item): float => max(0, (float) ($item->line_total ?? 0)));
        if ($weights->sum() <= 0) {
            $weights = $items->map(fn ($item): float => max(1, (int) $item->quantity));
        }
        $totalWeight = $weights->sum();
        $remainingAmount = $amount;
        $lastIndex = $items->count() - 1;

        foreach ($items as $index => $item) {
            $lineTotal = $index === $lastIndex
                ? $remainingAmount
                : round($amount * ($weights[$index] / $totalWeight), 2);
            $remainingAmount = round($remainingAmount - $lineTotal, 2);

            $item->update([
                'unit_price' => round($lineTotal / max(1, $item->quantity), 2),
                'line_total' => $lineTotal,
            ]);
        }

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

    private function showProps(Order $order, bool $editMode): array
    {
        $order->load(['items.design', 'items.project', 'items.size', 'user', 'invoice']);

        return [
            'order' => $order,
            'editMode' => $editMode,
            'uploadedFiles' => $editMode ? [] : $this->uploadedFilesForOrder($order),
        ];
    }

    private function uploadedFilesForOrder(Order $order): array
    {
        $designFiles = $order->items
            ->values()
            ->flatMap(function ($item, int $itemIndex): array {
                $paths = $item->customer_design_paths ?: [$item->customer_design_path];

                return collect($paths)
                    ->filter()
                    ->values()
                    ->map(function (string $path, int $fileIndex) use ($itemIndex): array {
                        $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);

                        return [
                            'id' => $itemIndex.'-'.$fileIndex,
                            'item_label' => 'Item '.($itemIndex + 1),
                            'name' => basename($path),
                            'url' => url('storage/'.$path),
                            'preview_url' => null,
                            'is_image' => $isImage,
                        ];
                    })
                    ->all();
            });

        $projects = $order->items
            ->map(fn ($item) => $item->project)
            ->filter()
            ->merge(CustomerProject::query()->where('order_id', $order->id)->get())
            ->unique('id');

        $projectFiles = $projects
            ->flatMap(function (CustomerProject $project): array {
                $paths = collect($project->source_paths ?: [$project->source_path])
                    ->filter()
                    ->values();

                return $paths->map(function (string $path, int $fileIndex) use ($project): array {
                    $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);

                    return [
                        'id' => 'project-'.$project->id.'-'.$fileIndex,
                        'item_label' => $project->title,
                        'name' => basename($path),
                        'url' => route('admin.projects.source', ['project' => $project, 'source' => $fileIndex]),
                        'preview_url' => $isImage
                            ? route('admin.projects.source-preview', ['project' => $project, 'source' => $fileIndex])
                            : null,
                        'is_image' => $isImage,
                    ];
                })->all();
            });

        return $designFiles
            ->merge($projectFiles)
            ->values()
            ->all();
    }
}
