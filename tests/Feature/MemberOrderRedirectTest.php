<?php

namespace Tests\Feature;

use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    private function createOrder(User $member, string $orderNo): Order
    {
        return Order::query()->create([
            'order_no' => $orderNo,
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Customer',
            'material' => 'Mirrorcote',
            'status' => 'pending',
            'subtotal' => 100,
            'total' => 100,
        ]);
    }
}
