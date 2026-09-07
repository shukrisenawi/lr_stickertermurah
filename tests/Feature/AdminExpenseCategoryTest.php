<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminExpenseCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_expense_categories_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.expense-categories.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ExpenseCategories/Index')
                ->has('categories', 0)
            );
    }

    public function test_admin_can_create_update_and_delete_expense_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $storeResponse = $this->actingAs($admin)->post(route('admin.expense-categories.store'), [
            'name' => 'Bahan Mentah',
        ]);

        $storeResponse
            ->assertRedirect(route('admin.expense-categories.index'))
            ->assertSessionHas('success', 'Kategori duit keluar berjaya ditambah.');
        $category = ExpenseCategory::query()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.expense-categories.update', $category), [
                'name' => 'Bahan Cetak',
            ])
            ->assertRedirect(route('admin.expense-categories.index'))
            ->assertSessionHas('success', 'Kategori duit keluar berjaya dikemaskini.');
        $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'name' => 'Bahan Cetak',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.expense-categories.destroy', $category))
            ->assertRedirect(route('admin.expense-categories.index'))
            ->assertSessionHas('success', 'Kategori duit keluar berjaya dipadam.');
        $this->assertDatabaseMissing('expense_categories', ['id' => $category->id]);
    }

    public function test_deleting_category_keeps_expense_and_clears_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = ExpenseCategory::query()->create(['name' => 'Penghantaran']);
        $expense = Expense::query()->create([
            'created_by' => $admin->id,
            'expense_category_id' => $category->id,
            'description' => 'Kos penghantaran',
            'amount' => 8.00,
            'purchase_date' => '2026-09-07',
        ]);

        $this->actingAs($admin)->delete(route('admin.expense-categories.destroy', $category));

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'expense_category_id' => null,
        ]);
    }

    public function test_expense_category_names_must_be_unique(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        ExpenseCategory::query()->create(['name' => 'Utiliti']);

        $this->actingAs($admin)
            ->post(route('admin.expense-categories.store'), ['name' => 'Utiliti'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('expense_categories', 1);
    }

    public function test_non_admin_cannot_manage_expense_categories(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('admin.expense-categories.index'))
            ->assertRedirect(route('admin.login'));
        $this->actingAs($customer)
            ->post(route('admin.expense-categories.store'), ['name' => 'Tidak Sah'])
            ->assertRedirect(route('admin.login'));
    }
}
