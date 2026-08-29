<?php

namespace Tests\Feature;

use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MemberOrderRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_open_order_form_inside_member_area(): void
    {
        $member = User::factory()->create(['is_admin' => false]);

        $this->actingAs($member)
            ->get(route('member.orders.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('memberMode', true)
            );
    }

    public function test_repeating_order_opens_form_inside_member_area(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($member, 'ORD-REPEAT');

        $this->actingAs($member)
            ->post(route('member.orders.repeat', $order))
            ->assertRedirect(route('member.orders.repeat-form', $order));

        $this->get(route('member.orders.repeat-form', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('memberMode', true)
            );
    }

    public function test_member_thank_you_page_uses_member_layout_page(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($member, 'ORD-THANK-YOU');

        $this->actingAs($member)
            ->get(route('orders.thank-you', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Member/OrderThankYou')
                ->where('order.id', $order->id)
            );
    }

    public function test_member_can_edit_order_item_while_order_is_pending(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($member, 'ORD-MEMBER-ITEM-EDIT', 'pending');
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 10,
            'unit_price' => 2,
            'line_total' => 20,
            'cut_type' => 'standard',
        ]);

        $this->actingAs($member)
            ->from(route('member.orders.show', $order))
            ->put(route('member.orders.items.update', ['order' => $order, 'item' => $item]), [
                'design_id' => null,
                'project_id' => null,
                'size_id' => null,
                'custom_design_description' => 'Design dikemaskini',
                'requested_size' => '5cm x 10cm',
                'quantity' => 25,
                'cut_type' => 'die-cut',
            ])
            ->assertRedirect(route('member.orders.show', $order))
            ->assertSessionHas('success', 'Item order berjaya dikemaskini.');

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'custom_design_description' => 'Design dikemaskini',
            'requested_size' => '5cm x 10cm',
            'quantity' => 25,
            'line_total' => 50,
            'cut_type' => 'die-cut',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 50,
            'total' => 50,
        ]);
    }

    public function test_member_cannot_edit_order_item_after_pending_status(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($member, 'ORD-MEMBER-ITEM-LOCKED', 'processing');
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 10,
            'unit_price' => 2,
            'line_total' => 20,
            'cut_type' => 'standard',
        ]);

        $this->actingAs($member)
            ->from(route('member.orders.show', $order))
            ->put(route('member.orders.items.update', ['order' => $order, 'item' => $item]), [
                'quantity' => 25,
                'cut_type' => 'standard',
            ])
            ->assertRedirect(route('member.orders.show', $order))
            ->assertSessionHas('error', 'Item hanya boleh dikemaskini ketika order menunggu semakan.');

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'quantity' => 10,
            'line_total' => 20,
        ]);
    }

    public function test_member_orders_route_lists_only_authenticated_member_orders(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $otherMember = User::factory()->create(['is_admin' => false]);
        $memberOrder = $this->createOrder($member, 'ORD-MEMBER');
        $this->createOrder($otherMember, 'ORD-OTHER');

        $this->actingAs($member)
            ->get(route('member.orders.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Member/Orders/Index')
                ->has('orders.data', 1)
                ->where('orders.data.0.id', $memberOrder->id)
                ->where('orders.data.0.order_no', $memberOrder->order_no)
            );
    }

    public function test_member_navigation_counts_orders_awaiting_price_approval(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $otherMember = User::factory()->create(['is_admin' => false]);
        $awaitingOrder = $this->createOrder($member, 'ORD-AWAITING-PRICE');
        $awaitingOrder->update(['pricing_status' => 'awaiting_customer_approval']);
        $approvedOrder = $this->createOrder($member, 'ORD-APPROVED-PRICE');
        $approvedOrder->update(['pricing_status' => 'approved']);
        $otherAwaitingOrder = $this->createOrder($otherMember, 'ORD-OTHER-AWAITING');
        $otherAwaitingOrder->update(['pricing_status' => 'awaiting_customer_approval']);

        $this->actingAs($member)
            ->get(route('member.orders.show', $awaitingOrder))
            ->assertInertia(fn (Assert $page) => $page
                ->where('orderCounts.memberAwaitingApproval', 1)
            );
    }

    public function test_member_can_cancel_order_after_receiving_admin_price(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($member, 'ORD-CANCEL-AWAITING');
        $order->update(['pricing_status' => 'awaiting_customer_approval']);

        $this->actingAs($member)
            ->from(route('member.orders.index'))
            ->post(route('member.orders.cancel', $order))
            ->assertRedirect(route('member.orders.index'))
            ->assertSessionHas('success', 'Order ORD-CANCEL-AWAITING berjaya dibatalkan.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'pricing_status' => 'cancelled',
        ]);
    }

    public function test_member_cannot_cancel_order_before_admin_price_or_after_invoice(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $pendingOrder = $this->createOrder($member, 'ORD-CANCEL-PENDING');

        $this->actingAs($member)
            ->from(route('member.orders.index'))
            ->post(route('member.orders.cancel', $pendingOrder))
            ->assertRedirect(route('member.orders.index'))
            ->assertSessionHas('error', 'Order hanya boleh dibatalkan selepas menerima harga admin dan sebelum diluluskan.');

        $pendingOrder->update(['pricing_status' => 'awaiting_customer_approval']);
        $pendingOrder->invoice()->create([
            'user_id' => $member->id,
            'invoice_no' => 'INV-CANCEL-TEST',
            'issue_date' => now()->toDateString(),
            'amount' => 100,
            'customer_name' => $member->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Customer',
        ]);

        $this->actingAs($member)
            ->from(route('member.orders.index'))
            ->post(route('member.orders.cancel', $pendingOrder))
            ->assertRedirect(route('member.orders.index'))
            ->assertSessionHas('error', 'Order yang sudah mempunyai invoice tidak boleh dibatalkan.');

        $this->assertDatabaseHas('orders', [
            'id' => $pendingOrder->id,
            'status' => 'pending',
        ]);
    }

    public function test_member_order_form_only_lists_watermarked_order_project_images(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($member, 'ORD-PROJECT');
        $project = CustomerProject::query()->create([
            'user_id' => $member->id,
            'order_id' => $order->id,
            'title' => 'Design Customer',
            'preview_path' => 'customer-projects/previews/design.jpg',
            'preview_paths' => [
                'customer-projects/previews/design.jpg',
                'customer-projects/previews/design.webp',
                'customer-projects/previews/design.pdf',
            ],
            'source_path' => 'customer-projects/sources/design.ai',
            'source_paths' => ['customer-projects/sources/design.ai'],
        ]);
        CustomerProject::query()->create([
            'user_id' => $member->id,
            'order_id' => null,
            'title' => 'Project Tanpa Order',
            'preview_path' => 'customer-projects/previews/standalone.pdf',
            'preview_paths' => ['customer-projects/previews/standalone.pdf'],
            'source_path' => 'customer-projects/sources/standalone.ai',
            'source_paths' => ['customer-projects/sources/standalone.ai'],
        ]);

        $this->actingAs($member)
            ->get(route('member.orders.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('previousProjects', 1)
                ->where('previousProjects.0.id', $project->id)
                ->where('previousProjects.0.preview_url', route('member.projects.preview', ['project' => $project, 'preview' => 0]))
            );
    }

    public function test_member_order_form_lists_previous_order_preview_images_with_sizes(): void
    {
        Storage::fake('local');
        $member = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($member, 'ORD-PREVIOUS-IMAGE');
        $size = StickerSize::query()->create([
            'name' => '5cm x 5cm',
            'width_cm' => 5,
            'height_cm' => 5,
            'price' => 0,
            'qty_per_a3' => 10,
            'is_active' => true,
        ]);
        $previewPaths = [
            'order-items/previews/previous-satu.webp',
            'order-items/previews/previous-dua.webp',
        ];
        foreach ($previewPaths as $previewPath) {
            Storage::disk('local')->put($previewPath, 'preview');
        }
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'sticker_size_id' => $size->id,
            'custom_design_description' => 'Design Lama',
            'quantity' => 200,
            'cut_type' => 'standard',
            'customer_preview_path' => $previewPaths[0],
            'customer_preview_paths' => $previewPaths,
            'unit_price' => 1,
            'line_total' => 200,
        ]);

        $this->actingAs($member)
            ->get(route('member.orders.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('previousOrderDesigns', 2)
                ->where('previousOrderDesigns.0.id', $item->id)
                ->where('previousOrderDesigns.0.preview_index', 0)
                ->where('previousOrderDesigns.0.title', 'Design Lama')
                ->where('previousOrderDesigns.0.size_name', '5cm x 5cm')
                ->where('previousOrderDesigns.0.quantity', 200)
                ->where('previousOrderDesigns.0.preview_url', route('member.orders.items.preview', ['order' => $order, 'item' => $item, 'preview' => 0]))
                ->where('previousOrderDesigns.1.preview_index', 1)
                ->where('previousOrderDesigns.1.preview_url', route('member.orders.items.preview', ['order' => $order, 'item' => $item, 'preview' => 1]))
            );
    }

    private function createOrder(User $member, string $orderNo, string $status = 'pending'): Order
    {
        return Order::query()->create([
            'order_no' => $orderNo,
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Customer',
            'material' => 'Mirrorcote',
            'status' => $status,
            'subtotal' => 100,
            'total' => 100,
        ]);
    }
}
