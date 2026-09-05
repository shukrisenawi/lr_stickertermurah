<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\CustomerProject;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceSetting;
use App\Models\Setting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_index_has_only_pending_and_completed_groups(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $this->createOrder($customer, 'ORD-PENDING', 'pending');
        $this->createOrder($customer, 'ORD-PROCESSING', 'processing');
        $completedOrder = $this->createOrder($customer, 'ORD-COMPLETED', 'completed');

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['status' => 'pending']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.status', 'pending')
                ->has('orders.data', 2)
                ->where('orderCounts.adminPending', 1)
                ->where('adminNotifications.0.key', 'orders-pending')
                ->where('adminNotifications.0.count', 1)
            );

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['status' => 'completed']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.status', 'completed')
                ->has('orders.data', 1)
                ->where('orders.data.0.order_no', $completedOrder->order_no)
            );
    }

    public function test_admin_order_index_includes_customer_id_for_login_action(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-LOGIN-ACTION', 'pending');

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('orders.data.0.id', $order->id)
                ->where('orders.data.0.user.id', $customer->id)
                ->where('orders.data.0.user.is_admin', false)
            );
    }

    public function test_admin_can_delete_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-DELETE', 'pending');

        $response = $this->actingAs($admin)
            ->delete(route('admin.orders.destroy', $order));

        $response->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('success', 'Order berjaya dipadam.');
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_admin_order_form_lists_customers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->get(route('admin.orders.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('adminMode', true)
                ->where('customers.0.id', $customer->id)
            );
    }

    public function test_admin_order_form_prefills_requested_customer_and_address(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $address = CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Order',
            'address' => 'Alamat Order',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.create', ['user_id' => $customer->id, 'address_id' => $address->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('initialCustomerId', $customer->id)
                ->where('initialAddressId', $address->id)
            );
    }

    public function test_admin_can_open_order_edit_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-EDIT', 'pending');

        $this->actingAs($admin)
            ->get(route('admin.orders.edit', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Show')
                ->where('order.id', $order->id)
                ->where('itemEditEnabled', true)
            );
    }

    public function test_admin_order_show_page_allows_item_editing_without_edit_mode(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-SHOW-ITEM-EDIT', 'pending');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Show')
                ->where('order.id', $order->id)
                ->where('editMode', false)
                ->where('itemEditEnabled', true)
            );
    }

    public function test_admin_can_add_common_size_from_order_context(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-COMMON-SIZE', 'pending');

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.sizes.store'), [
                'name' => 'Design Common',
                'width_cm' => 3,
                'height_cm' => 10,
                'shape' => 'Segi Empat Sama',
                'return_to_order' => true,
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('success', 'Saiz berjaya ditambah ke database umum.');

        $this->assertDatabaseHas('sticker_sizes', [
            'name' => 'Design Common',
            'width_cm' => 3,
            'height_cm' => 10,
            'shape' => 'Segi Empat Sama',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_order_item(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $category = Category::query()->create(['name' => 'Edit Test', 'slug' => 'edit-test']);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Baharu',
            'is_active' => true,
        ]);
        $size = StickerSize::query()->create([
            'name' => 'Saiz Baharu',
            'width_cm' => 5,
            'height_cm' => 5,
            'price' => 0,
            'qty_per_a3' => 10,
            'is_active' => true,
        ]);
        $order = $this->createOrder($customer, 'ORD-ITEM-EDIT', 'pending');
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'cut_type' => 'standard',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.orders.items.update', ['order' => $order, 'item' => $item]), [
                'design_id' => $design->id,
                'size_id' => $size->id,
                'custom_design_description' => 'Arahan design baharu',
                'requested_size' => '',
                'quantity' => 250,
                'cut_type' => 'die-cut',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Item order berjaya dikemaskini.');

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'sticker_design_id' => $design->id,
            'sticker_size_id' => $size->id,
            'quantity' => 250,
            'line_total' => 250,
            'cut_type' => 'die-cut',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 250,
            'total' => 250,
            'balance_due' => 250,
        ]);
    }

    public function test_tracking_number_sets_order_status_to_shipped_from_status_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-TRACKING-FORM', 'pending');
        Setting::setValue('n8n_webhook_url', 'https://example.test/n8n');
        Http::fake();

        $this->actingAs($admin)
            ->put(route('admin.orders.update', $order), [
                'status' => 'processing',
                'tracking_no' => ' JNT123456789 ',
            ])
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'tracking_no' => 'JNT123456789',
        ]);

        Http::assertNotSent(fn (Request $request): bool => $request->url() === 'https://example.test/n8n');
    }

    public function test_admin_can_add_tracking_from_order_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-TRACKING-MODAL', 'processing');
        Setting::setValue('n8n_webhook_url', 'https://example.test/n8n');
        Http::fake();

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->put(route('admin.orders.tracking.update', $order), [
                'tracking_no' => 'JNT987654321',
            ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('success', 'No. tracking berjaya disimpan. Status order ditetapkan sebagai sedang dihantar.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'tracking_no' => 'JNT987654321',
        ]);

        Http::assertNothingSent();
    }

    public function test_completing_order_with_invoice_tracking_notifies_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-TRACKING-COMPLETE', 'shipped');
        $order->update(['tracking_no' => 'JNT111222333']);
        Invoice::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'invoice_no' => 'INV-TRACKING-COMPLETE',
            'issue_date' => now()->toDateString(),
            'amount' => 100,
            'payment_status' => 'paid',
            'tracking_no' => 'JNT111222333',
        ]);
        Setting::setValue('n8n_webhook_url', 'https://example.test/n8n');
        Http::fake();

        $this->actingAs($admin)
            ->put(route('admin.orders.update', $order), ['status' => 'completed'])
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/n8n'
            && data_get($request->data(), 'type') === 'tracking_updated'
            && data_get($request->data(), 'tracking_no') === 'JNT111222333'
            && data_get($request->data(), 'status') === 'completed');
        $notification = $customer->notifications()->latest()->first();
        $this->assertNotNull($notification);
        $this->assertSame('tracking', data_get($notification->data, 'type'));
        $this->assertStringContainsString('JNT111222333', data_get($notification->data, 'message'));
    }

    public function test_uploaded_design_files_are_visible_on_order_view_and_edit_item(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-FILES', 'pending');
        OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'cut_type' => 'standard',
            'customer_design_path' => 'customer-designs/design-satu.pdf',
            'customer_design_paths' => [
                'customer-designs/design-satu.pdf',
                'customer-designs/design-dua.png',
            ],
        ]);
        CustomerProject::query()->create([
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'title' => 'Project Lama',
            'preview_path' => '',
            'preview_paths' => [],
            'source_path' => 'customer-projects/sources/project-lama.pdf',
            'source_paths' => ['customer-projects/sources/project-lama.pdf'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('editMode', false)
                ->where('uploadedFiles.0.item_label', 'Bil. 1 - Design sendiri | Saiz custom | Qty 100')
                ->where('uploadedFiles.0.name', 'design-satu.pdf')
                ->where('uploadedFiles.0.origin', 'create_order')
                ->where('uploadedFiles.0.origin_label', 'Upload masa create order')
                ->where('uploadedFiles.1.name', 'design-dua.png')
                ->where('uploadedFiles.2.name', 'project-lama.pdf')
                ->where('uploadedFiles.2.file_type_label', 'Fail project')
                ->has('uploadedFiles', 3)
            );

        $this->actingAs($admin)
            ->get(route('admin.orders.edit', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('editMode', true)
                ->has('uploadedFiles', 0)
                ->has('order.items.0.files', 2)
                ->where('order.items.0.files.0.type', 'design')
                ->where('order.items.0.files.0.name', 'design-satu.pdf')
            );
    }

    public function test_admin_can_upload_private_source_and_preview_files_for_an_order_item(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-ITEM-FILES', 'processing');
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'cut_type' => 'standard',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.items.files.store', ['order' => $order, 'item' => $item]), [
                'source_files' => [
                    UploadedFile::fake()->create('design.ai', 100, 'application/octet-stream'),
                    UploadedFile::fake()->create('design.pdf', 100, 'application/pdf'),
                ],
                'preview_images' => [
                    UploadedFile::fake()->image('preview-satu.jpg', 800, 600),
                    UploadedFile::fake()->image('preview-dua.jpg', 400, 900),
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Fail item berjaya dimuat naik.');

        $item->refresh();
        $this->assertNotNull($item->admin_source_path);
        $this->assertNotNull($item->customer_preview_path);
        $this->assertCount(2, $item->admin_source_paths);
        $this->assertCount(2, $item->customer_preview_paths);
        $this->assertTrue(Storage::disk('local')->exists($item->admin_source_path));
        $this->assertTrue(Storage::disk('local')->exists($item->customer_preview_path));
        $previewDimensions = getimagesize(Storage::disk('local')->path($item->customer_preview_path));
        $this->assertLessThanOrEqual(400, $previewDimensions[0]);
        $this->assertLessThanOrEqual(1000, $previewDimensions[1]);

        $this->actingAs($admin)
            ->get(route('admin.orders.items.source', ['order' => $order, 'item' => $item, 'source' => 1]))
            ->assertOk();
        $this->actingAs($customer)
            ->get(route('member.orders.items.preview', ['order' => $order, 'item' => $item, 'preview' => 1]))
            ->assertOk()
            ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
            ->assertHeader('Pragma', 'no-cache');
        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->has('uploadedFiles', 4)
                ->where('uploadedFiles.0.origin', 'admin')
                ->where('uploadedFiles.0.file_type_label', 'Fail source admin')
                ->where('uploadedFiles.2.origin', 'admin')
                ->where('uploadedFiles.2.file_type_label', 'Gambar preview customer')
            );

        $oldPreviewPath = $item->customer_preview_paths[1];
        $this->actingAs($admin)
            ->delete(route('admin.orders.items.files.destroy', [
                'order' => $order,
                'item' => $item,
                'type' => 'preview',
                'index' => 1,
            ]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Fail item berjaya dipadam.');

        $item->refresh();
        $this->assertCount(1, $item->customer_preview_paths);
        $this->assertFalse(Storage::disk('local')->exists($oldPreviewPath));
        $this->actingAs($admin)
            ->get(route('admin.orders.edit', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->has('order.items.0.files', 3)
                ->where('order.items.0.files.0.type', 'source')
                ->where('order.items.0.files.2.type', 'preview')
            );
        $this->actingAs($otherCustomer)
            ->get(route('member.orders.items.preview', ['order' => $order, 'item' => $item]))
            ->assertForbidden();
    }

    public function test_admin_can_use_the_same_image_for_source_and_customer_preview(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-SAME-IMAGE-FILES', 'processing');
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'cut_type' => 'standard',
        ]);
        $image = UploadedFile::fake()->image('same-image.png', 800, 600);

        $this->actingAs($admin)
            ->post(route('admin.orders.items.files.store', ['order' => $order, 'item' => $item]), [
                'source_files' => [$image],
                'preview_images' => [$image],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Fail item berjaya dimuat naik.');

        $item->refresh();
        $this->assertCount(1, $item->admin_source_paths);
        $this->assertCount(1, $item->customer_preview_paths);
        $this->assertTrue(Storage::disk('local')->exists($item->admin_source_path));
        $this->assertTrue(Storage::disk('local')->exists($item->customer_preview_path));
        $previewDimensions = getimagesize(Storage::disk('local')->path($item->customer_preview_path));
        $this->assertLessThanOrEqual(400, $previewDimensions[0]);
    }

    public function test_uploading_admin_files_for_all_items_marks_order_completed(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-AUTO-COMPLETE', 'processing');
        $firstItem = OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'cut_type' => 'standard',
        ]);
        $secondItem = OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 50,
            'unit_price' => 1,
            'line_total' => 50,
            'cut_type' => 'standard',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.items.files.store', ['order' => $order, 'item' => $firstItem]), [
                'source_files' => [UploadedFile::fake()->create('design-pertama.ai', 100, 'application/octet-stream')],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.items.files.store', ['order' => $order, 'item' => $secondItem]), [
                'source_files' => [UploadedFile::fake()->create('design-kedua.ai', 100, 'application/octet-stream')],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_create_order_for_selected_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat penghantaran',
            'custom_description' => 'Design custom customer',
            'requested_size' => '5cm x 5cm',
            'quantity' => 100,
            'cut_type' => 'standard',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
        ]);
    }

    public function test_admin_can_create_order_with_free_shipping(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $category = Category::query()->create(['name' => 'Test', 'slug' => 'test']);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Free Shipping',
            'is_active' => true,
        ]);
        $size = StickerSize::query()->create([
            'name' => 'Saiz Free Shipping',
            'width_cm' => 5,
            'height_cm' => 5,
            'price' => 0,
            'qty_per_a3' => 10,
            'is_active' => true,
        ]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat free shipping',
            'design_id' => $design->id,
            'size_id' => $size->id,
            'quantity' => 100,
            'cut_type' => 'standard',
            'shipping_free' => true,
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $invoice = Invoice::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertTrue($order->shipping_free);
        $this->assertSame('0.00', (string) $order->shipping_fee);
        $this->assertSame('120.00', (string) $order->total);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Pos - Semenanjung Malaysia (Percuma)',
            'line_total' => 0,
        ]);
    }

    public function test_admin_can_mark_existing_quote_as_free_shipping(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-FREE-QUOTE', 'pending');
        $order->update([
            'shipping_region' => 'sabah_sarawak',
            'shipping_fee' => 12,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'cut_type' => 'standard',
        ]);

        $this->actingAs($admin)->post(route('admin.orders.quote', $order), [
            'amount' => 100,
            'shipping_free' => true,
        ])->assertRedirect();

        $order->refresh();
        $this->assertTrue($order->shipping_free);
        $this->assertSame('0.00', (string) $order->shipping_fee);
        $this->assertSame('100.00', (string) $order->total);
    }

    public function test_admin_order_creation_auto_creates_invoice_and_redirects_to_confirmation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $category = Category::query()->create(['name' => 'Test', 'slug' => 'test']);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Test',
            'is_active' => true,
        ]);
        $size = StickerSize::query()->create([
            'name' => 'Saiz Test',
            'width_cm' => 5,
            'height_cm' => 5,
            'price' => 0,
            'qty_per_a3' => 10,
            'is_active' => true,
        ]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat penghantaran',
            'design_id' => $design->id,
            'size_id' => $size->id,
            'quantity' => 100,
            'cut_type' => 'standard',
        ]);

        $order = Order::query()->latest('id')->firstOrFail();
        $invoice = Invoice::query()->where('order_id', $order->id)->firstOrFail();
        $address = CustomerAddress::query()->where('user_id', $customer->id)->where('address', 'Alamat penghantaran')->firstOrFail();

        $response->assertRedirect(route('admin.invoices.edit', $invoice))
            ->assertSessionHas('success');
        $this->assertSame('127.00', (string) $invoice->amount);
        $this->assertSame($address->id, $order->customer_address_id);
        $this->assertSame($address->id, $invoice->customer_address_id);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'quantity' => 100,
            'line_total' => 120,
        ]);
    }

    public function test_admin_order_creation_does_not_send_n8n_notification(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        Setting::setValue('n8n_webhook_url', 'https://example.test/n8n');
        Http::fake();

        $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat penghantaran',
            'custom_description' => 'Design custom customer',
            'requested_size' => '5cm x 5cm',
            'quantity' => 100,
            'cut_type' => 'standard',
        ])->assertRedirect();

        Http::assertNothingSent();
    }

    private function createOrder(User $customer, string $orderNo, string $status): Order
    {
        return Order::query()->create([
            'order_no' => $orderNo,
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Customer',
            'material' => 'Mirrorcote',
            'status' => $status,
            'subtotal' => 100,
            'total' => 100,
        ]);
    }
}
