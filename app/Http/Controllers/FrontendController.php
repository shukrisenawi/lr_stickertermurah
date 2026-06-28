<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\PriceSetting;
use App\Models\StickerDesign;
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

        $categories->each(function ($category) {
            $category->designs->each(function ($design) {
                $design->image_url = $design->image_path
                    ? Storage::disk('public')->url($design->image_path)
                    : null;
            });
        });

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

        $priceSettings = PriceSetting::query()
            ->where('is_active', true)
            ->orderBy('sticker_type')
            ->orderBy('qty_from')
            ->get();

        $paymentSettings = PaymentSetting::query()->first();
        if ($paymentSettings && $paymentSettings->qr_image_path) {
            $paymentSettings->qr_image_url = Storage::disk('public')->url($paymentSettings->qr_image_path);
        }

        $customerAddresses = Auth::user()?->customerAddresses()->get() ?? collect();
        $latestCustomerAddress = $customerAddresses->first()?->address;

        return Inertia::render('Public/OrderForm', [
            'designs' => $designs,
            'sizes' => $sizes,
            'priceSettings' => $priceSettings,
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

    public function priceChecker(): Response
    {
        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $priceSettings = PriceSetting::query()
            ->where('is_active', true)
            ->orderBy('sticker_type')
            ->orderBy('qty_from')
            ->get();

        $stickerTypes = PriceSetting::query()
            ->where('is_active', true)
            ->select('sticker_type')
            ->distinct()
            ->pluck('sticker_type')
            ->toArray();

        $paymentSettings = PaymentSetting::query()->first();

        $tableSizes = [2.5, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $tableQuantities = [100, 200, 300, 500, 1000, 2000, 3000, 5000];

        $mirrorcoteTiers = PriceSetting::query()
            ->where('sticker_type', 'Mirrorcote')
            ->where('is_active', true)
            ->orderBy('qty_from')
            ->get()
            ->toArray();

        $priceTable = [];
        foreach ($tableSizes as $size) {
            $qtyPerA3 = (int) (floor(42 / $size) * floor(29.7 / $size));
            if ($qtyPerA3 < 1) {
                $qtyPerA3 = 1;
            }
            $row = ['size' => $size, 'qty_per_a3' => $qtyPerA3];
            foreach ($tableQuantities as $qty) {
                $a3Sheets = (int) ceil($qty / $qtyPerA3);
                $total = null;
                foreach ($mirrorcoteTiers as $tier) {
                    if ($a3Sheets >= $tier['qty_from'] && ($tier['qty_to'] === null || $a3Sheets <= $tier['qty_to'])) {
                        $total = round($a3Sheets * $tier['price_per_a3'], 2);
                        break;
                    }
                }
                $row[(string) $qty] = $total;
            }
            $priceTable[] = $row;
        }

        return Inertia::render('Public/PriceChecker', [
            'sizes' => $sizes,
            'priceSettings' => $priceSettings,
            'stickerTypes' => $stickerTypes,
            'paymentSettings' => $paymentSettings,
            'priceTable' => $priceTable,
            'priceTableQuantities' => $tableQuantities,
        ]);
    }
}
