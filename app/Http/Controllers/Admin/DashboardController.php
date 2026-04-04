<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\StickerDesign;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->whereIn('status', ['pending', 'paid', 'processing'])->count(),
            'totalDesigns' => StickerDesign::query()->count(),
            'totalCategories' => Category::query()->count(),
            'recentOrders' => Order::query()->latest()->limit(10)->get(),
        ]);
    }
}
