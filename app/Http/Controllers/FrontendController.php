<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use Illuminate\View\View;

class FrontendController extends Controller
{
    public function home(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->with(['designs' => fn ($query) => $query->where('is_active', true)->latest()])
            ->orderBy('name')
            ->get();

        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('frontend.home', [
            'categories' => $categories,
            'sizes' => $sizes,
        ]);
    }

    public function orderForm(?Order $repeatOrder = null): View
    {
        $designs = StickerDesign::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('frontend.order-form', [
            'designs' => $designs,
            'sizes' => $sizes,
            'repeatOrder' => $repeatOrder?->load('items'),
        ]);
    }

    public function lookupForm(): View
    {
        return view('frontend.lookup-order');
    }
}
