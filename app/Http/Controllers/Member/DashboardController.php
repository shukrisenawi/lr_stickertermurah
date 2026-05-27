<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PriceSetting;
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

        $priceSettings = PriceSetting::query()
            ->where('is_active', true)
            ->orderBy('sticker_type')
            ->orderBy('qty_from')
            ->get();

        return Inertia::render('Member/Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => Order::query()->where('user_id', $userId)->count(),
            'totalInvoices' => Order::query()->where('user_id', $userId)->has('invoice')->count(),
            'activeSizes' => StickerSize::query()->where('is_active', true)->count(),
            'priceSettings' => $priceSettings,
        ]);
    }
}
