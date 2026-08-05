<?php

namespace App\Http\Controllers;

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

        $homeLimit = 8;
        $designsQuery = StickerDesign::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name');

        $designsTotal = $designsQuery->count();

        $designs = $designsQuery
            ->take($homeLimit)
            ->get()
            ->map(function ($design) {
                $imageUrl = $design->image_path
                    ? Storage::disk('public')->url($design->image_path)
                    : null;

                return [
                    'id' => $design->id,
                    'name' => $design->name,
                    'category' => $design->category?->name ?? 'Lain-lain',
                    'tags' => $design->tags ?? [],
                    'image' => $imageUrl,
                    'mobile_image' => $design->mobile_image_path
                        ? Storage::disk('public')->url($design->mobile_image_path)
                        : $imageUrl,
                ];
            });

        $categoryCounts = StickerDesign::query()
            ->where('sticker_designs.is_active', true)
            ->whereNotNull('category_id')
            ->join('categories', 'categories.id', '=', 'sticker_designs.category_id')
            ->where('categories.is_active', true)
            ->groupBy('categories.name')
            ->orderBy('categories.name')
            ->selectRaw('categories.name as name, COUNT(*) as count')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        $allTagCounts = StickerDesign::query()
            ->where('is_active', true)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->map(fn ($count, $tag) => ['name' => $tag, 'count' => $count])
            ->values()
            ->toArray();

        return Inertia::render('Public/Home', [
            'testimonials' => $testimonials,
            'designs' => $designs,
            'designs_total' => $designsTotal,
            'designs_limit' => $homeLimit,
            'categories' => $categoryCounts,
            'tags' => $allTagCounts,
        ]);
    }

    public function orderForm(Request $request, ?Order $repeatOrder = null): Response
    {
        if ($repeatOrder && $repeatOrder->user_id !== Auth::id()) {
            abort(403);
        }

        $repeatOrder = $repeatOrder?->load(['items.design', 'items.size']);
        $repeatDesignId = $repeatOrder?->items->first()?->sticker_design_id;
        $requestedDesignId = $request->integer('design_id');
        $selectedDesignId = $repeatDesignId ?: ($requestedDesignId > 0 ? $requestedDesignId : null);

        $selectedDesign = StickerDesign::query()
            ->where('is_active', true)
            ->with('category')
            ->find($selectedDesignId);

        $selectedDesign = $selectedDesign ? [
            'id' => $selectedDesign->id,
            'name' => $selectedDesign->name,
            'image_url' => $selectedDesign->image_path
                ? Storage::disk('public')->url($selectedDesign->image_path)
                : null,
            'mobile_image_url' => $selectedDesign->mobile_image_path
                ? Storage::disk('public')->url($selectedDesign->mobile_image_path)
                : ($selectedDesign->image_path ? Storage::disk('public')->url($selectedDesign->image_path) : null),
            'category' => $selectedDesign->category?->name,
            'tags' => $selectedDesign->tags ?? [],
        ] : null;

        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $previousDesigns = StickerDesign::query()
            ->whereHas('orderItems.order', fn ($query) => $query->where('user_id', Auth::id()))
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($design) {
                return [
                    'id' => $design->id,
                    'name' => $design->name,
                    'image_url' => null,
                    'mobile_image_url' => null,
                    'category' => $design->category?->name,
                    'tags' => $design->tags ?? [],
                ];
            });

        $catalogTags = StickerDesign::query()
            ->where('is_active', true)
            ->whereNotNull('tags')
            ->get(['tags'])
            ->flatMap(fn ($design) => $design->tags ?? [])
            ->filter()
            ->map(fn ($tag) => (string) $tag)
            ->unique()
            ->sort()
            ->values()
            ->all();

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
            'initialDesign' => $selectedDesign,
            'sizes' => $sizes,
            'previousDesigns' => $previousDesigns,
            'catalogTags' => $catalogTags,
            'priceSettings' => $priceSettings,
            'paymentSettings' => $paymentSettings,
            'repeatOrder' => $repeatOrder,
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
