<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FrontendController extends Controller
{
    public function home(): Response
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

        return Inertia::render('Public/Home', [
            'categories' => $categories,
            'sizes' => $sizes,
        ]);
    }

    public function orderForm(Request $request, ?Order $repeatOrder = null): Response
    {
        if ($repeatOrder && $repeatOrder->user_id !== Auth::id()) {
            abort(403);
        }

        $selectedDesignId = (int) $request->integer('design_id');

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

        $customerAddresses = Auth::user()?->customerAddresses()->get() ?? collect();
        $latestCustomerAddress = $customerAddresses->first()?->address;

        return Inertia::render('Public/OrderForm', [
            'designs' => $designs,
            'sizes' => $sizes,
            'repeatOrder' => $repeatOrder?->load('items'),
            'selectedDesignId' => $selectedDesignId,
            'customerAddresses' => $customerAddresses,
            'latestCustomerAddress' => $latestCustomerAddress,
        ]);
    }

    public function lookupForm(): Response
    {
        return Inertia::render('Public/LookupOrder');
    }
}
