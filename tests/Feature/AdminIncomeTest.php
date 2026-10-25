<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminIncomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_page_only_counts_income_records_and_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        ExpenseCategory::query()->create(['name' => 'Perbelanjaan']);
        Expense::query()->create(['description' => 'Keluar', 'amount' => 999, 'purchase_date' => '2026-10-25']);
        $category = IncomeCategory::query()->create(['name' => 'Jualan']);
        Income::query()->create([
            'income_category_id' => $category->id, 'description' => 'Tunai', 'amount' => 50, 'received_date' => '2026-10-25',
        ]);

        $this->actingAs($admin)->get(route('admin.incomes.index'))
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Incomes/Index')
                ->has('incomes.data', 1)->where('totalAmount', 50)->where('totalCount', 1)
                ->has('categories', 1)->where('categories.0.name', 'Jualan')
                ->where('incomes.data.0.received_date', '2026-10-25'));
    }

    public function test_admin_can_create_edit_and_delete_income_with_private_receipt(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = IncomeCategory::query()->create(['name' => 'Jualan']);
        $data = [
            'income_category_id' => $category->id, 'description' => 'Bayaran tunai',
            'amount' => '125.50', 'received_date' => '2026-10-25', 'notes' => 'Bayaran diterima',
        ];
        $this->actingAs($admin)->post(route('admin.incomes.store'), $data + [
            'receipt' => UploadedFile::fake()->image('bukti.png'),
        ])->assertRedirect(route('admin.incomes.index'))->assertSessionHasNoErrors();
        $income = Income::query()->firstOrFail();
        $this->assertSame($admin->id, $income->created_by);
        $this->assertSame('125.50', $income->amount);
        $path = $income->receipt_path;
        Storage::disk('local')->assertExists($path);
        $this->get(route('admin.incomes.receipt.download', $income))->assertDownload('bukti.png');
        $this->get(route('admin.incomes.receipt.preview', $income))->assertOk()->assertHeader('Content-Type', 'image/png');

        $this->put(route('admin.incomes.update', $income), array_replace($data, ['amount' => '150.00']))
            ->assertRedirect(route('admin.incomes.index'))->assertSessionHasNoErrors();
        $this->assertSame('150.00', $income->fresh()->amount);
        $this->assertSame($path, $income->fresh()->receipt_path);

        $this->post(route('admin.incomes.update', $income), $data + [
            '_method' => 'PUT', 'receipt' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('admin.incomes.index'))->assertSessionHasNoErrors();
        $income->refresh();
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertExists($income->receipt_path);
        $this->get(route('admin.incomes.receipt.download', $income))->assertDownload('bukti.pdf');
        $this->get(route('admin.incomes.receipt.preview', $income))->assertNotFound();
        $this->delete(route('admin.incomes.destroy', $income))->assertRedirect();
        Storage::disk('local')->assertMissing($income->receipt_path);
        $this->assertDatabaseCount('incomes', 0);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_income_requires_valid_category_amount_date_and_receipt(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = ExpenseCategory::query()->create(['name' => 'Kategori Keluar']);
        $this->actingAs($admin)->post(route('admin.incomes.store'), [
            'income_category_id' => $category->id, 'description' => '', 'amount' => '-1',
            'received_date' => 'invalid', 'receipt' => UploadedFile::fake()->create('bad.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors(['income_category_id', 'description', 'amount', 'received_date', 'receipt']);
        $this->assertDatabaseCount('incomes', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_non_admin_cannot_manage_income_or_access_receipts(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $income = Income::query()->create(['description' => 'Sulit', 'amount' => 10, 'received_date' => '2026-10-25']);
        $this->actingAs($customer);
        $this->get(route('admin.incomes.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.incomes.store'), [])->assertRedirect(route('admin.login'));
        $this->put(route('admin.incomes.update', $income), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.incomes.destroy', $income))->assertRedirect(route('admin.login'));
        $this->get(route('admin.incomes.receipt.download', $income))->assertRedirect(route('admin.login'));
        $this->get(route('admin.incomes.receipt.preview', $income))->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_income_pages(): void
    {
        $this->get(route('admin.incomes.index'))->assertRedirect('/login');
        $this->get(route('admin.income-categories.index'))->assertRedirect('/login');
    }
}
