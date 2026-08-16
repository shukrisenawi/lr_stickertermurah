<?php

namespace Tests\Feature;

use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderProjectFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_page_only_lists_projects_for_the_order_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        $order = $this->orderFor($customer);
        $customerProject = $this->projectFor($customer, 'Customer File');
        $this->projectFor($otherCustomer, 'Other Customer File');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('customerProjects.0.id', $customerProject->id)
                ->where('customerProjects.0.title', 'Customer File')
                ->has('customerProjects', 1)
            );
    }

    public function test_admin_can_upload_a_project_file_for_an_order(): void
    {
        Storage::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->orderFor($customer);

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.store', $order), [
                'title' => 'Design Baru Customer',
                'files' => [UploadedFile::fake()->image('design-baru.png')],
            ])
            ->assertRedirect();

        $project = CustomerProject::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail()->refresh();

        $this->assertSame($customer->id, $project->user_id);
        $this->assertSame($order->id, $project->order_id);
        $this->assertSame($project->id, $item->customer_project_id);
        $this->assertNull($item->sticker_design_id);
        Storage::assertExists($project->source_path);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('customerProjects.0.source_files.0.is_image', true)
                ->where('customerProjects.0.source_files.0.preview_url', route('admin.projects.source-preview', ['project' => $project, 'source' => 0]))
            );

        $this->actingAs($admin)
            ->get(route('admin.projects.source-preview', ['project' => $project, 'source' => 0]))
            ->assertOk();
    }

    public function test_admin_can_select_a_previous_project_only_for_the_same_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        $order = $this->orderFor($customer);
        $customerProject = $this->projectFor($customer, 'Design Lama');
        $otherProject = $this->projectFor($otherCustomer, 'Design Orang Lain');

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $customerProject->id,
                'source_index' => 0,
            ])
            ->assertRedirect();

        $item = $order->items()->firstOrFail()->refresh();
        $this->assertSame($customerProject->id, $item->customer_project_id);
        $this->assertSame(0, $item->customer_project_source_index);

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $otherProject->id,
                'source_index' => 0,
            ])
            ->assertSessionHasErrors('project_id');

        $this->assertSame($customerProject->id, $item->refresh()->customer_project_id);
    }

    private function orderFor(User $customer): Order
    {
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Customer',
            'material' => 'Mirrorcote',
            'status' => 'pending',
            'subtotal' => 100,
            'total' => 100,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'cut_type' => 'standard',
        ]);

        return $order;
    }

    private function projectFor(User $customer, string $title): CustomerProject
    {
        return CustomerProject::query()->create([
            'user_id' => $customer->id,
            'title' => $title,
            'preview_path' => '',
            'preview_paths' => [],
            'source_path' => 'customer-projects/sources/'.strtolower(str_replace(' ', '-', $title)).'.ai',
            'source_paths' => ['customer-projects/sources/'.strtolower(str_replace(' ', '-', $title)).'.ai'],
        ]);
    }
}
