<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminIncomeCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_income_categories_separately_from_expenses(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        ExpenseCategory::query()->create(['name' => 'Jualan']);
        $this->actingAs($admin)->post(route('admin.income-categories.store'), ['name' => 'Jualan'])
            ->assertRedirect(route('admin.income-categories.index'))->assertSessionHasNoErrors();
        $category = IncomeCategory::query()->firstOrFail();
        $this->get(route('admin.income-categories.index'))->assertInertia(fn (Assert $page) => $page
            ->component('Admin/IncomeCategories/Index')->has('categories', 1)->where('categories.0.incomes_count', 0));
        $this->post(route('admin.income-categories.store'), ['name' => 'Jualan'])->assertSessionHasErrors('name');
        $this->put(route('admin.income-categories.update', $category), ['name' => 'Modal'])
            ->assertRedirect(route('admin.income-categories.index'))->assertSessionHasNoErrors();
        $this->assertSame('Modal', $category->fresh()->name);
        $income = Income::query()->create([
            'income_category_id' => $category->id, 'description' => 'Modal pemilik',
            'amount' => 100, 'received_date' => '2026-10-25',
        ]);
        $this->delete(route('admin.income-categories.destroy', $category))->assertRedirect(route('admin.income-categories.index'));
        $this->assertNull($income->fresh()->income_category_id);
        $this->assertDatabaseCount('incomes', 1);
        $this->assertDatabaseCount('income_categories', 0);
        $this->assertDatabaseCount('expense_categories', 1);
    }

    public function test_category_name_is_required_and_unique_on_update(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = IncomeCategory::query()->create(['name' => 'Jualan']);
        IncomeCategory::query()->create(['name' => 'Modal']);
        $this->actingAs($admin)->post(route('admin.income-categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->put(route('admin.income-categories.update', $category), ['name' => 'Modal'])->assertSessionHasErrors('name');
        $this->assertSame('Jualan', $category->fresh()->name);
    }

    public function test_non_admin_cannot_manage_income_categories(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $category = IncomeCategory::query()->create(['name' => 'Jualan']);
        $this->actingAs($customer)->get(route('admin.income-categories.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.income-categories.store'), ['name' => 'Tidak sah'])->assertRedirect(route('admin.login'));
        $this->put(route('admin.income-categories.update', $category), ['name' => 'Tidak sah'])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.income-categories.destroy', $category))->assertRedirect(route('admin.login'));
    }
}
