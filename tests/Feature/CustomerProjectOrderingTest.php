<?php

namespace Tests\Feature;

use App\Models\CustomerProject;
use App\Models\Order;
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
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame($project->id, $item->customer_project_id);
        $this->assertNull($item->sticker_design_id);
        $this->assertSame($project->title, $item->custom_design_description);
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
        return CustomerProject::query()->create([
            'user_id' => $member->id,
            'title' => 'Design Kedai Test',
            'preview_path' => 'customer-projects/previews/design.jpg',
            'preview_paths' => ['customer-projects/previews/design.jpg'],
            'source_path' => 'customer-projects/sources/design.ai',
            'source_paths' => ['customer-projects/sources/design.ai'],
        ]);
    }
}
