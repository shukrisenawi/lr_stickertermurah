<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\User;
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
        return Inertia::render('Admin/Orders/Show', $this->showProps($order));
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

    private function showProps(Order $order): array
    {
        return [
            'order' => $order->load(['items.design', 'items.project', 'items.size', 'user', 'invoice']),
            'customerProjects' => $this->customerProjectsForOrder($order),
        ];
    }

    private function customerProjectsForOrder(Order $order): array
    {
        if (! $order->user_id && $this->normalizePhone($order->customer_phone) === null) {
            return [];
        }

        $projectQuery = CustomerProject::query()->with('order');
        if ($order->user_id) {
            $projectQuery->where('user_id', $order->user_id);
        } else {
            $phone = $this->normalizePhone($order->customer_phone);
            if ($phone === null) {
                return [];
            }

            $userIds = User::query()
                ->where('is_admin', false)
                ->get(['id', 'no_tel'])
                ->filter(fn (User $user): bool => $this->normalizePhone($user->no_tel) === $phone)
                ->pluck('id');

            if ($userIds->isEmpty()) {
                return [];
            }

            $projectQuery->whereIn('user_id', $userIds);
        }

        return $projectQuery
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
                        ->map(function (string $path, int $index) use ($project): array {
                            $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);

                            return [
                                'index' => $index,
                                'name' => basename($path),
                                'url' => route('admin.projects.source', ['project' => $project, 'source' => $index]),
                                'is_image' => $isImage,
                                'preview_url' => $isImage
                                    ? route('admin.projects.source-preview', ['project' => $project, 'source' => $index])
                                    : null,
                            ];
                        })
                        ->all(),
                    'created_at' => $project->created_at,
                    'order_no' => $project->order?->order_no,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '60'.substr($digits, 1);
        }

        return preg_match('/^60\d{8,12}$/', $digits) === 1 ? $digits : null;
    }
}
