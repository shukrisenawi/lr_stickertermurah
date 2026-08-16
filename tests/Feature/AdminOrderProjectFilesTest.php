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

    public function test_admin_order_without_user_link_lists_and_selects_projects_by_phone(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false, 'no_tel' => '+60123456789']);
        $order = $this->orderFor($customer);
        $order->update([
            'user_id' => null,
            'customer_phone' => '0123456789',
        ]);
        $project = $this->projectFor($customer, 'Project Ikut Telefon');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('customerProjects.0.id', $project->id)
                ->has('customerProjects', 1)
            );

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $project->id,
                'source_indices' => [0],
            ])
            ->assertRedirect();

        $this->assertSame($project->id, $order->items()->firstOrFail()->refresh()->customer_project_id);
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

    public function test_uploading_new_files_keeps_selected_files_from_the_current_project(): void
    {
        Storage::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->orderFor($customer);
        $project = $this->projectFor($customer, 'Project Lama');
        $project->update([
            'source_paths' => [
                'customer-projects/sources/lama-1.png',
                'customer-projects/sources/lama-2.pdf',
            ],
        ]);
        Storage::put('customer-projects/sources/lama-1.png', 'old image');
        Storage::put('customer-projects/sources/lama-2.pdf', 'old pdf');

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $project->id,
                'source_indices' => [0],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.store', $order), [
                'title' => 'Tambahan Fail',
                'files' => [UploadedFile::fake()->image('tambahan.png')],
            ])
            ->assertRedirect();

        $item = $order->items()->firstOrFail()->refresh();
        $newProject = CustomerProject::query()->latest('id')->firstOrFail();

        $this->assertSame($project->id, $item->customer_project_id);
        $this->assertSame([
            ['project_id' => $project->id, 'source_indices' => [0]],
            ['project_id' => $newProject->id, 'source_indices' => [0]],
        ], $item->customer_project_sources);
        Storage::assertExists($newProject->source_paths[0]);
    }

    public function test_admin_can_select_a_previous_project_only_for_the_same_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        $order = $this->orderFor($customer);
        $customerProject = $this->projectFor($customer, 'Design Lama');
        $customerProject->update([
            'source_paths' => [
                'customer-projects/sources/design-lama.ai',
                'customer-projects/sources/design-lama.pdf',
            ],
        ]);
        $otherProject = $this->projectFor($otherCustomer, 'Design Orang Lain');

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $customerProject->id,
                'source_indices' => [0, 1],
            ])
            ->assertRedirect();

        $item = $order->items()->firstOrFail()->refresh();
        $this->assertSame($customerProject->id, $item->customer_project_id);
        $this->assertSame(0, $item->customer_project_source_index);
        $this->assertSame([0, 1], $item->customer_project_source_indices);

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $otherProject->id,
                'source_indices' => [0],
            ])
            ->assertSessionHasErrors('project_id');

        $this->assertSame($customerProject->id, $item->refresh()->customer_project_id);

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $customerProject->id,
                'source_indices' => [],
            ])
            ->assertRedirect();

        $item = $item->refresh();
        $this->assertNull($item->customer_project_source_index);
        $this->assertSame([], $item->customer_project_source_indices);
    }

    public function test_admin_keeps_files_when_selecting_from_multiple_projects(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->orderFor($customer);
        $firstProject = $this->projectFor($customer, 'Project Pertama');
        $secondProject = $this->projectFor($customer, 'Project Kedua');

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $firstProject->id,
                'source_indices' => [0],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $secondProject->id,
                'source_indices' => [0],
            ])
            ->assertRedirect();

        $item = $order->items()->firstOrFail()->refresh();
        $this->assertSame([
            ['project_id' => $firstProject->id, 'source_indices' => [0]],
            ['project_id' => $secondProject->id, 'source_indices' => [0]],
        ], $item->customer_project_sources);
    }

    public function test_admin_can_remove_a_selected_project_file_from_the_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->orderFor($customer);
        $project = $this->projectFor($customer, 'Design Dengan Banyak Fail');
        $project->update([
            'source_paths' => [
                'customer-projects/sources/design-1.png',
                'customer-projects/sources/design-2.png',
                'customer-projects/sources/design-3.png',
            ],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.projects.select', $order), [
                'project_id' => $project->id,
                'source_indices' => [0, 1, 2],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->delete(route('admin.orders.projects.source.destroy', ['order' => $order, 'project' => $project, 'source' => 1]))
            ->assertRedirect();

        $item = $order->items()->firstOrFail()->refresh();
        $this->assertSame(0, $item->customer_project_source_index);
        $this->assertSame([0, 2], $item->customer_project_source_indices);
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
