<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\PriceSetting;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerProjectOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_open_order_form_with_a_previous_project_selected(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $project = $this->projectFor($member);

        $this->actingAs($member)
            ->get(route('orders.create', ['project_id' => $project->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('initialProject.id', $project->id)
                ->where('initialProject.title', $project->title)
                ->where('initialProject.customer_address_id', $project->customer_address_id)
                ->where('previousProjects.0.id', $project->id)
            );
    }

    public function test_member_can_submit_an_order_for_a_previous_project(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $project = $this->projectFor($member);

        $this->actingAs($member)
            ->post(route('orders.store'), [
                'project_id' => $project->id,
                'size_id' => null,
                'requested_size' => '5x5cm',
                'quantity' => 100,
                'cut_type' => 'standard',
                'customer_name' => 'Customer Project',
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat Project',
                'order_note' => 'Pastikan warna ikut preview.',
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame($project->id, $item->customer_project_id);
        $this->assertNull($item->sticker_design_id);
        $this->assertSame($project->title, $item->custom_design_description);
        $this->assertSame('Pastikan warna ikut preview.', $order->custom_request);
        $this->assertSame($project->customer_address_id, $order->customer_address_id);
    }

    public function test_priced_project_order_creates_invoice_with_sabah_sarawak_shipping(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $project = $this->projectFor($member);
        $size = StickerSize::query()->create([
            'name' => '5cm x 5cm',
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

        $this->actingAs($member)
            ->post(route('orders.store'), [
                'project_id' => $project->id,
                'size_id' => $size->id,
                'quantity' => 100,
                'cut_type' => 'standard',
                'shipping_region' => 'sabah_sarawak',
                'customer_name' => 'Customer Project',
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat Project',
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $invoice = $order->invoice()->with('items')->firstOrFail();

        $this->assertSame('120.00', (string) $order->subtotal);
        $this->assertSame('12.00', (string) $order->shipping_fee);
        $this->assertSame('132.00', (string) $order->total);
        $this->assertSame('132.00', (string) $invoice->amount);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Pos - Sabah & Sarawak',
            'line_total' => 12,
        ]);
    }

    public function test_member_cannot_submit_another_members_project(): void
    {
        $owner = User::factory()->create(['is_admin' => false]);
        $member = User::factory()->create(['is_admin' => false]);
        $project = $this->projectFor($owner);

        $this->actingAs($member)
            ->post(route('orders.store'), [
                'project_id' => $project->id,
                'quantity' => 100,
                'cut_type' => 'standard',
                'customer_name' => 'Customer Project',
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat Project',
            ])
            ->assertForbidden();
    }

    private function projectFor(User $member): CustomerProject
    {
        $address = CustomerAddress::query()->create([
            'user_id' => $member->id,
            'recipient_name' => 'Customer Project',
            'address' => 'Alamat Project',
            'no_hp' => '60123456789',
            'is_default' => true,
        ]);

        return CustomerProject::query()->create([
            'user_id' => $member->id,
            'customer_address_id' => $address->id,
            'title' => 'Design Kedai Test',
            'preview_path' => 'customer-projects/previews/design.jpg',
            'preview_paths' => ['customer-projects/previews/design.jpg'],
            'source_path' => 'customer-projects/sources/design.ai',
            'source_paths' => ['customer-projects/sources/design.ai'],
        ]);
    }
}
