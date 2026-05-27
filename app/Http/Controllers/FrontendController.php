<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\StickerDesign;
use App\Models\StickerPriceTier;
use App\Models\StickerSize;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        // Add image_url to each design
        $categories->each(function ($category) {
            $category->designs->each(function ($design) {
                $design->image_url = $design->image_path
                    ? Storage::disk('public')->url($design->image_path)
                    : null;
            });
        });

        // Flat list of all active designs for the design gallery
        $allDesigns = StickerDesign::query()
            ->where('is_active', true)
            ->with('category')
            ->latest()
            ->take(30)
            ->get()
            ->map(function ($design) {
                $design->image_url = $design->image_path
                    ? Storage::disk('public')->url($design->image_path)
                    : null;

                return $design;
            });

        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        // Approved testimonials for homepage (latest 3)
        $testimonials = Testimonial::query()
            ->where('is_approved', true)
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($t) {
                $t->image_url = $t->image_path
                    ? Storage::disk('public')->url($t->image_path)
                    : null;

                return $t;
            });

        return Inertia::render('Public/Home', [
            'categories' => $categories,
            'allDesigns' => $allDesigns,
            'sizes' => $sizes,
            'testimonials' => $testimonials,
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
            ->get()
            ->map(function ($design) {
                $design->image_url = $design->image_path
                    ? Storage::disk('public')->url($design->image_path)
                    : null;

                return $design;
            });

        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $priceTiers = StickerPriceTier::query()
            ->with('size')
            ->get()
            ->groupBy('sticker_size_id')
            ->map(fn ($tiers) => $tiers->map(fn ($t) => ['quantity' => $t->quantity, 'total_price' => $t->total_price])->values());

        $paymentSettings = PaymentSetting::query()->first();
        if ($paymentSettings && $paymentSettings->qr_image_path) {
            $paymentSettings->qr_image_url = Storage::disk('public')->url($paymentSettings->qr_image_path);
        }

        $customerAddresses = Auth::user()?->customerAddresses()->get() ?? collect();
        $latestCustomerAddress = $customerAddresses->first()?->address;

        return Inertia::render('Public/OrderForm', [
            'designs' => $designs,
            'sizes' => $sizes,
            'priceTiers' => $priceTiers,
            'paymentSettings' => $paymentSettings,
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
