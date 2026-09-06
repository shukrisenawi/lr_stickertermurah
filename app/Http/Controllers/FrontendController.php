<?php

namespace App\Http\Controllers;

use App\Models\CustomerProject;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\PriceSetting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\StickerPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FrontendController extends Controller
{
    public function __construct(private readonly StickerPricingService $stickerPricing) {}

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

        $homeLimit = 27;
        $designsQuery = StickerDesign::query()
            ->where('is_active', true)
            ->with('category')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

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

        $startingA3Sheets = $this->stickerPricing->minimumA3SheetsWithoutDesign();
        $startingPriceSetting = PriceSetting::query()
            ->where('is_active', true)
            ->where('sticker_type', 'Mirrorcote')
            ->where('qty_from', '<=', $startingA3Sheets)
            ->where(function ($query) use ($startingA3Sheets): void {
                $query->where('qty_to', '>=', $startingA3Sheets)
                    ->orWhereNull('qty_to');
            })
            ->orderBy('qty_from')
            ->first();

        return Inertia::render('Public/Home', [
            'testimonials' => $testimonials,
            'designs' => $designs,
            'designs_total' => $designsTotal,
            'designs_limit' => $homeLimit,
            'categories' => $categoryCounts,
            'tags' => $allTagCounts,
            'starting_price' => $startingPriceSetting
                ? round($startingA3Sheets * (float) $startingPriceSetting->price_per_a3, 2)
                : null,
            'starting_a3_sheets' => $startingA3Sheets,
        ]);
    }

    public function orderForm(Request $request, ?Order $repeatOrder = null): Response
    {
        $adminMode = $request->routeIs('admin.orders.create');

        if ($repeatOrder && $repeatOrder->user_id !== Auth::id()) {
            abort(403);
        }

        $adminCustomers = $adminMode
            ? User::query()
                ->where('is_admin', false)
                ->with(['customerAddresses' => function ($query): void {
                    $query->orderByDesc('is_default')->orderByDesc('updated_at');
                }])
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'no_tel' => $user->no_tel,
                    'discount_amount' => (float) $user->discount_amount,
                    'discount_forever' => (bool) $user->discount_forever,
                    'addresses' => $user->customerAddresses->map(fn ($address): array => [
                        'id' => $address->id,
                        'recipient_name' => $address->recipient_name,
                        'address' => $address->address,
                        'no_hp' => $address->no_hp,
                        'is_default' => $address->is_default,
                    ])->values()->all(),
                ])
                ->values()
                ->all()
            : [];
        $requestedCustomerId = $adminMode ? $request->integer('user_id') : 0;
        $requestedAddressId = $adminMode ? $request->integer('address_id') : 0;
        $initialCustomer = collect($adminCustomers)->firstWhere('id', $requestedCustomerId);
        $initialAddress = $initialCustomer !== null
            ? collect($initialCustomer['addresses'])->firstWhere('id', $requestedAddressId)
            : null;
        $initialCustomerId = $initialCustomer['id'] ?? null;
        $initialAddressId = $initialAddress['id']
            ?? ($initialCustomer !== null ? ($initialCustomer['addresses'][0]['id'] ?? null) : null);

        $repeatOrder = $repeatOrder?->load(['items.design', 'items.project', 'items.size']);
        $repeatItem = $repeatOrder?->items->first();
        if ($repeatItem) {
            $repeatItem->setAttribute('has_design', $this->stickerPricing->hasExistingDesign($repeatItem));
            $previewPath = collect($repeatItem->customer_preview_paths ?: [$repeatItem->customer_preview_path])
                ->filter()
                ->first();
            $repeatItem->setAttribute(
                'repeat_preview_url',
                $previewPath
                    ? route('member.orders.items.preview', ['order' => $repeatOrder, 'item' => $repeatItem, 'preview' => 0])
                    : null,
            );
        }

        $repeatDesignId = $repeatItem?->sticker_design_id;
        $requestedDesignId = $request->integer('design_id');
        $requestedProjectId = $request->integer('project_id');
        $customerProjects = ! $adminMode && Auth::check()
            ? CustomerProject::query()
                ->where('user_id', Auth::id())
                ->latest()
                ->get()
            : collect();
        $initialProject = $customerProjects->firstWhere(
            'id',
            $requestedProjectId > 0 ? $requestedProjectId : $repeatItem?->customer_project_id,
        );
        $selectedDesignId = $initialProject
            ? null
            : ($repeatDesignId ?: ($requestedDesignId > 0 ? $requestedDesignId : null));

        $selectedDesign = StickerDesign::query()
            ->where(function ($query) use ($selectedDesignId): void {
                $query->where('is_active', true);
                if ($selectedDesignId) {
                    $query->orWhere('id', $selectedDesignId);
                }
            })
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

        $previousDesigns = $adminMode
            ? collect()
            : StickerDesign::query()
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

        $previousOrderDesigns = $adminMode
            ? collect()
            : OrderItem::query()
                ->where(function ($query): void {
                    $query->whereNotNull('customer_preview_path')
                        ->orWhereNotNull('customer_preview_paths');
                })
                ->whereHas('order', fn ($query) => $query->where('user_id', Auth::id()))
                ->with(['order', 'design', 'project', 'size'])
                ->latest('order_items.updated_at')
                ->limit(30)
                ->get()
                ->flatMap(function (OrderItem $item): array {
                    $title = $item->design?->name
                        ?: $item->project?->title
                        ?: $item->custom_design_description
                        ?: 'Design sendiri';
                    $previewPaths = collect($item->customer_preview_paths ?: [$item->customer_preview_path])
                        ->filter()
                        ->values();

                    return $previewPaths->map(function (string $path, int $previewIndex) use ($item, $title): array {
                        return [
                            'id' => $item->id,
                            'preview_index' => $previewIndex,
                            'title' => $title,
                            'preview_url' => route('member.orders.items.preview', ['order' => $item->order, 'item' => $item, 'preview' => $previewIndex]),
                            'order_no' => $item->order?->order_no,
                            'size_id' => $item->sticker_size_id,
                            'size_name' => $item->size?->name ?: $item->requested_size ?: 'Saiz custom',
                            'requested_size' => $item->requested_size,
                            'quantity' => (int) $item->quantity,
                            'cut_type' => $item->cut_type,
                        ];
                    })->all();
                })
                ->values();

        $previousProjects = $customerProjects->map(function (CustomerProject $project) {
            $previewPaths = collect($project->preview_paths ?: ($project->preview_path ? [$project->preview_path] : []))
                ->filter(fn ($path): bool => is_string($path) && $this->isCustomerPreviewImage($path))
                ->values();

            return [
                'id' => $project->id,
                'title' => $project->title,
                'notes' => $project->notes,
                'customer_address_id' => $project->customer_address_id,
                'preview_url' => $previewPaths->isNotEmpty()
                    ? route('member.projects.preview', ['project' => $project, 'preview' => 0])
                    : null,
                'created_at' => $project->created_at,
            ];
        })->filter(fn (array $project): bool => $project['preview_url'] !== null)->values();

        $initialProject = $initialProject ? [
            'id' => $initialProject->id,
            'title' => $initialProject->title,
            'notes' => $initialProject->notes,
            'customer_address_id' => $initialProject->customer_address_id,
            'preview_url' => $previousProjects->firstWhere('id', $initialProject->id)['preview_url'] ?? null,
            'created_at' => $initialProject->created_at,
        ] : null;

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

        $customerAddresses = $adminMode ? collect() : (Auth::user()?->customerAddresses()->get() ?? collect());
        $latestCustomerAddress = $customerAddresses->first()?->address;

        return Inertia::render('Public/OrderForm', [
            'adminMode' => $adminMode,
            'initialCustomerId' => $initialCustomerId,
            'initialAddressId' => $initialAddressId,
            'customers' => $adminCustomers,
            'memberMode' => $request->routeIs('member.orders.create', 'member.orders.repeat-form'),
            'initialDesign' => $selectedDesign,
            'initialProject' => $initialProject,
            'sizes' => $sizes,
            'previousDesigns' => $previousDesigns,
            'previousOrderDesigns' => $previousOrderDesigns,
            'previousProjects' => $previousProjects,
            'catalogTags' => $catalogTags,
            'priceSettings' => $priceSettings,
            'minimumA3SheetsWithoutDesign' => $this->stickerPricing->minimumA3SheetsWithoutDesign(),
            'paymentSettings' => $paymentSettings,
            'repeatOrder' => $repeatOrder,
            'customerAddresses' => $customerAddresses,
            'latestCustomerAddress' => $latestCustomerAddress,
            'customerDiscountAmount' => ! $adminMode && Auth::user()?->discount_forever
                ? (float) Auth::user()->discount_amount
                : 0,
        ]);
    }

    public function lookupForm(): Response
    {
        return Inertia::render('Public/LookupOrder');
    }

    public function priceChecker(): Response
    {
        $leadingSizeNumber = static function (StickerSize $size): float {
            preg_match('/^\s*(\d+(?:[.,]\d+)?)/', $size->name, $matches);

            return isset($matches[1]) ? (float) str_replace(',', '.', $matches[1]) : INF;
        };

        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->orderBy('width_cm')
            ->orderBy('height_cm')
            ->orderBy('name')
            ->get()
            ->sort(function (StickerSize $first, StickerSize $second) use ($leadingSizeNumber): int {
                return $leadingSizeNumber($first) <=> $leadingSizeNumber($second)
                    ?: (($first->width_cm ?? INF) <=> ($second->width_cm ?? INF))
                    ?: (($first->height_cm ?? INF) <=> ($second->height_cm ?? INF))
                    ?: strcmp($first->name, $second->name);
            })
            ->values();

        $priceSettings = PriceSetting::query()
            ->where('is_active', true)
            ->orderBy('sticker_type')
            ->orderBy('qty_from')
            ->get();

        $discounts = Discount::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', today());
            })
            ->orderBy('min_qty')
            ->get(['id', 'name', 'sticker_type', 'sticker_size_id', 'min_qty', 'max_qty', 'type', 'value'])
            ->map(fn (Discount $discount): array => [
                'id' => $discount->id,
                'name' => $discount->name,
                'sticker_type' => $discount->sticker_type,
                'sticker_size_id' => $discount->sticker_size_id,
                'min_qty' => (int) $discount->min_qty,
                'max_qty' => $discount->max_qty !== null ? (int) $discount->max_qty : null,
                'type' => $discount->type,
                'value' => (float) $discount->value,
            ])
            ->values()
            ->all();

        $stickerTypes = PriceSetting::query()
            ->where('is_active', true)
            ->select('sticker_type')
            ->distinct()
            ->pluck('sticker_type')
            ->toArray();

        $paymentSettings = PaymentSetting::query()->first();

        $tableQuantities = [100, 200, 300, 500, 1000, 2000, 3000, 5000];

        return Inertia::render('Public/PriceChecker', [
            'sizes' => $sizes,
            'priceSettings' => $priceSettings,
            'discounts' => $discounts,
            'stickerTypes' => $stickerTypes,
            'paymentSettings' => $paymentSettings,
            'priceTableQuantities' => $tableQuantities,
            'minimumA3SheetsWithoutDesign' => $this->stickerPricing->minimumA3SheetsWithoutDesign(),
        ]);
    }

    private function isCustomerPreviewImage(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
    }
}
