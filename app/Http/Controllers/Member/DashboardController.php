<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StickerPriceTier;
use App\Models\StickerSize;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $userId = Auth::id();

        $recentOrders = Order::query()
            ->where('user_id', $userId)
            ->with('invoice')
            ->latest()
            ->limit(5)
            ->get();

        $latestTiers = StickerPriceTier::query()
            ->with('size')
            ->get()
            ->groupBy('sticker_size_id')
            ->map(fn ($tiers) => $tiers->sortBy('quantity')->first())
            ->values()
            ->sortBy(fn ($tier) => $tier->size?->width_cm ?? 0)
            ->take(8);

        return Inertia::render('Member/Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => Order::query()->where('user_id', $userId)->count(),
            'totalInvoices' => Order::query()->where('user_id', $userId)->has('invoice')->count(),
            'activeSizes' => StickerSize::query()->where('is_active', true)->count(),
            'latestTiers' => $latestTiers,
        ]);
    }
}
