<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceSetting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderPricingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_order_form_with_selected_design(): void
    {
        $category = Category::query()->create(['name' => 'Test', 'slug' => 'test']);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Test',
            'is_active' => true,
        ]);

        $this->get(route('orders.create', ['design_id' => $design->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('initialDesign.id', $design->id)
                ->where('initialDesign.name', 'Design Test')
            );
    }

    public function test_member_auto_priced_order_creates_invoice_without_customer_approval(): void
    {
        [$member, $design, $size] = $this->productSetup();
        $size->update(['qty_per_a3' => 10]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $this->actingAs($member)->post(route('orders.store'), $this->orderData($design, $size, 100))->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame('auto_priced', $order->pricing_status);
        $this->assertSame('127.00', (string) $order->total);

        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'amount' => 127]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $order->invoice->id,
            'description' => 'Pos - Semenanjung Malaysia',
            'line_total' => 7,
        ]);
    }

    public function test_order_with_product_subtotal_of_rm150_gets_free_shipping(): void
    {
        [$member, $design, $size] = $this->productSetup();
        $size->update(['qty_per_a3' => 10]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 15,
            'is_active' => true,
        ]);

        $this->actingAs($member)
            ->post(route('orders.store'), $this->orderData($design, $size, 100))
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame('150.00', (string) $order->subtotal);
        $this->assertSame('0.00', (string) $order->shipping_fee);
        $this->assertSame('150.00', (string) $order->total);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $order->invoice->id,
            'description' => 'Pos - Semenanjung Malaysia (Percuma)',
            'line_total' => 0,
        ]);
    }

    public function test_admin_can_create_one_order_with_multiple_items(): void
    {
        [$customer, $design, $size] = $this->productSetup();
        $secondDesign = StickerDesign::query()->create([
            'category_id' => $design->category_id,
            'name' => 'Design Kedua',
            'is_active' => true,
        ]);
        $size->update(['qty_per_a3' => 10]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'customer_address_id' => null,
            'customer_name' => 'Customer Berbilang Item',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Berbilang Item',
            'design_id' => null,
            'project_id' => null,
            'custom_description' => null,
            'size_id' => null,
            'requested_size' => null,
            'quantity' => 1,
            'cut_type' => 'standard',
            'items' => [
                [
                    'design_id' => $design->id,
                    'size_id' => $size->id,
                    'quantity' => 100,
                    'cut_type' => 'standard',
                ],
                [
                    'design_id' => $secondDesign->id,
                    'size_id' => $size->id,
                    'quantity' => 20,
                    'cut_type' => 'standard',
                ],
            ],
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame('auto_priced', $order->pricing_status);
        $this->assertSame('151.00', (string) $order->total);
        $this->assertCount(2, $order->items);
        $this->assertDatabaseCount('invoice_items', 3);
        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'amount' => 151]);
    }

    public function test_member_can_create_one_order_with_multiple_items(): void
    {
        [$member, $design, $size] = $this->productSetup();
        $secondDesign = StickerDesign::query()->create([
            'category_id' => $design->category_id,
            'name' => 'Design Ahli Kedua',
            'is_active' => true,
        ]);
        $size->update(['qty_per_a3' => 10]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $this->actingAs($member)->post(route('orders.store'), [
            'customer_address_id' => null,
            'customer_name' => 'Ahli Berbilang Item',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Ahli Berbilang Item',
            'design_id' => null,
            'project_id' => null,
            'custom_description' => null,
            'size_id' => null,
            'requested_size' => null,
            'quantity' => 1,
            'cut_type' => 'standard',
            'items' => [
                [
                    'design_id' => $design->id,
                    'size_id' => $size->id,
                    'quantity' => 100,
                    'cut_type' => 'standard',
                ],
                [
                    'design_id' => $secondDesign->id,
                    'size_id' => $size->id,
                    'quantity' => 20,
                    'cut_type' => 'standard',
                ],
            ],
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame($member->id, $order->user_id);
        $this->assertSame('auto_priced', $order->pricing_status);
        $this->assertSame('151.00', (string) $order->total);
        $this->assertCount(2, $order->items);
        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'amount' => 151]);
    }

    public function test_member_can_upload_multiple_design_files_for_one_order_item(): void
    {
        [$member, $design, $size] = $this->productSetup();
        Storage::fake('public');

        $this->actingAs($member)->post(route('orders.store'), [
            'customer_address_id' => null,
            'customer_name' => 'Ahli Dengan Banyak Design',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Banyak Design',
            'design_id' => null,
            'project_id' => null,
            'custom_description' => null,
            'size_id' => null,
            'requested_size' => null,
            'quantity' => 1,
            'cut_type' => 'standard',
            'items' => [
                [
                    'design_id' => $design->id,
                    'size_id' => $size->id,
                    'quantity' => 100,
                    'cut_type' => 'standard',
                    'customer_design_images' => [
                        UploadedFile::fake()->create('design-satu.pdf', 100, 'application/pdf'),
                        UploadedFile::fake()->create('design-dua.pdf', 100, 'application/pdf'),
                    ],
                ],
            ],
        ])->assertRedirect();

        $item = Order::query()->latest('id')->firstOrFail()->items()->firstOrFail();
        $paths = $item->customer_design_paths;

        $this->assertCount(2, $paths);
        $this->assertSame($paths[0], $item->customer_design_path);
        $this->assertTrue(Storage::disk('public')->exists($paths[0]));
        $this->assertTrue(Storage::disk('public')->exists($paths[1]));
    }

    public function test_custom_price_requires_customer_approval_before_invoice(): void
    {
        [$member, $design, $size] = $this->productSetup();

        $this->actingAs($member)->post(route('orders.store'), $this->orderData($design, $size, 100))->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.invoices.store', $order))->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['order_id' => $order->id]);

        $this->actingAs($admin)->post(route('admin.orders.quote', $order), [
            'amount' => 88,
            'price_note' => 'Termasuk caj custom.',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('awaiting_customer_approval', $order->pricing_status);

        $this->actingAs($admin)->post(route('admin.invoices.store', $order))->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['order_id' => $order->id]);

        $this->actingAs($member)->post(route('member.orders.approve-price', $order))->assertRedirect();
        $this->assertSame('approved', $order->refresh()->pricing_status);
        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'amount' => 95]);
    }

    public function test_member_can_repeat_a_previous_order_item_image_and_keep_private_files(): void
    {
        [$member, $design, $size] = $this->productSetup();
        $size->update(['qty_per_a3' => 10]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $previousOrder = Order::query()->create([
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Lama',
            'material' => 'Mirrorcote',
            'status' => 'completed',
            'subtotal' => 120,
            'total' => 120,
        ]);
        $previousItem = OrderItem::query()->create([
            'order_id' => $previousOrder->id,
            'sticker_design_id' => $design->id,
            'sticker_size_id' => $size->id,
            'quantity' => 100,
            'cut_type' => 'standard',
            'admin_source_path' => 'order-items/sources/design.ai',
            'admin_source_paths' => [
                'order-items/sources/design.ai',
                'order-items/sources/design.pdf',
            ],
            'customer_preview_path' => 'order-items/previews/design.webp',
            'customer_preview_paths' => [
                'order-items/previews/design.webp',
                'order-items/previews/design-2.webp',
            ],
            'unit_price' => 1.2,
            'line_total' => 120,
        ]);

        $this->actingAs($member)
            ->post(route('orders.store'), [
                'customer_name' => $member->name,
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat Baru',
                'repeat_from_order_id' => $previousOrder->id,
                'quantity' => 1,
                'cut_type' => 'standard',
                'items' => [[
                    'previous_order_item_id' => $previousItem->id,
                    'size_id' => $size->id,
                    'quantity' => 100,
                    'cut_type' => 'standard',
                ]],
            ])
            ->assertRedirect();

        $newOrder = Order::query()->latest('id')->firstOrFail();
        $newItem = $newOrder->items()->firstOrFail();

        $this->assertSame($previousOrder->id, $newOrder->repeat_from_order_id);
        $this->assertSame($previousItem->admin_source_paths, $newItem->admin_source_paths);
        $this->assertSame($previousItem->customer_preview_paths, $newItem->customer_preview_paths);
        $this->assertSame($design->id, $newItem->sticker_design_id);
    }

    public function test_custom_size_order_can_be_submitted_without_sticker_size(): void
    {
        [$member, $design] = $this->productSetup();

        $response = $this->actingAs($member)->post(route('orders.store'), [
            'design_id' => $design->id,
            'size_id' => null,
            'requested_size' => '3x10',
            'quantity' => 100,
            'cut_type' => 'standard',
            'customer_name' => 'Customer Custom Size',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Custom Size',
        ]);

        $response->assertRedirect()->assertSessionMissing('success');

        $order = Order::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame('pending_admin', $order->pricing_status);
        $this->assertNull($item->sticker_size_id);
        $this->assertSame('3x10', $item->requested_size);
    }

    public function test_admin_can_quote_custom_size_with_a3_rate_for_customer_calculation(): void
    {
        [$member, $design] = $this->productSetup();
        $admin = User::factory()->create(['is_admin' => true]);
        PriceSetting::query()->create([
            'sticker_type' => 'Glossy',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $this->actingAs($member)->post(route('orders.store'), [
            'design_id' => $design->id,
            'size_id' => null,
            'requested_size' => 'Macam seblum ni',
            'quantity' => 100,
            'cut_type' => 'standard',
            'customer_name' => 'Customer Custom Quote',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Custom Quote',
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->has('priceSettings', 1)
                ->where('priceSettings.0.sticker_type', 'Glossy')
                ->where('priceSettings.0.price_per_a3', 12)
            );

        $this->actingAs($admin)->post(route('admin.orders.quote', $order), [
            'price_note' => 'Kiraan mengikut susunan A3.',
            'item_quotes' => [[
                'item_id' => $item->id,
                'qty_per_a3' => 24,
                'sticker_type' => 'Glossy',
            ]],
        ])->assertRedirect();

        $order->refresh();
        $item->refresh();

        $this->assertSame('awaiting_customer_approval', $order->pricing_status);
        $this->assertSame('60.00', (string) $order->subtotal);
        $this->assertSame('67.00', (string) $order->total);
        $this->assertSame(24, $item->quoted_qty_per_a3);
        $this->assertSame('12.00', (string) $item->quoted_price_per_a3);
        $this->assertSame('Glossy', $item->quoted_sticker_type);
        $this->assertSame('60.00', (string) $item->line_total);

        $this->actingAs($member)->get(route('member.orders.show', $order))
            ->assertInertia(fn ($page) => $page
                ->where('order.items.0.quoted_qty_per_a3', 24)
                ->where('order.items.0.quoted_price_per_a3', '12.00')
                ->where('order.items.0.quoted_sticker_type', 'Glossy')
            );

        $this->actingAs($member)->post(route('member.orders.approve-price', $order))->assertRedirect();

        $this->assertDatabaseHas('invoice_items', [
            'description' => 'Sticker : Macam seblum ni',
        ]);

        $invoice = $order->refresh()->invoice;
        $this->get(URL::temporarySignedRoute('invoices.public', now()->addDay(), ['invoice' => $invoice]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/InvoiceShow')
                ->where('invoice.custom_quotes.0.id', $item->id)
                ->where('invoice.custom_quotes.0.sticker_type', 'Glossy')
                ->where('invoice.custom_quotes.0.quoted_qty_per_a3', 24)
                ->where('invoice.custom_quotes.0.quoted_price_per_a3', 12)
            );
    }

    private function productSetup(): array
    {
        $member = User::factory()->create(['is_admin' => false]);
        $category = Category::query()->create(['name' => 'Test', 'slug' => 'test']);
        $design = StickerDesign::query()->create(['category_id' => $category->id, 'name' => 'Design Test', 'is_active' => true]);
        $size = StickerSize::query()->create([
            'name' => 'Saiz Test',
            'width_cm' => 5,
            'height_cm' => 5,
            'price' => 0,
            'qty_per_a3' => null,
            'is_active' => true,
        ]);

        return [$member, $design, $size];
    }

    private function orderData(StickerDesign $design, StickerSize $size, int $quantity): array
    {
        return [
            'design_id' => $design->id,
            'size_id' => $size->id,
            'quantity' => $quantity,
            'cut_type' => 'standard',
            'customer_name' => 'Customer Test',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Test',
        ];
    }
}
