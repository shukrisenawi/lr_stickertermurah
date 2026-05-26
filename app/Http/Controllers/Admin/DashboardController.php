<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\StickerDesign;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->whereIn('status', ['pending', 'paid', 'processing'])->count(),
            'totalDesigns' => StickerDesign::query()->count(),
            'totalCategories' => Category::query()->count(),
            'recentOrders' => Order::query()->latest()->limit(10)->get(),
        ]);
    }
}
