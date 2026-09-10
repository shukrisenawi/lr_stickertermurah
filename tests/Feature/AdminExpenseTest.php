<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_expenses_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.expenses.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Expenses/Index')
            ->has('expenses.data', 0)
            ->where('totalAmount', 0)
            ->where('totalCount', 0)
            ->has('today')
            ->where('maxReceiptSizeMb', 10)
            ->has('categories', 0)
        );
    }

    public function test_admin_can_store_expense_with_purchase_date_and_receipt(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = ExpenseCategory::query()->create(['name' => 'Pejabat']);
        $receipt = UploadedFile::fake()->image('resit-kertas.png', 120, 80);

        $response = $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'expense_category_id' => $category->id,
            'description' => 'Beli kertas printer',
            'amount' => '38.50',
            'purchase_date' => '2026-09-05',
            'notes' => 'Untuk kegunaan pejabat.',
            'receipt' => $receipt,
        ]);

        $response->assertRedirect(route('admin.expenses.index'));
        $expense = Expense::query()->firstOrFail();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'created_by' => $admin->id,
            'expense_category_id' => $category->id,
            'description' => 'Beli kertas printer',
            'amount' => '38.50',
            'receipt_original_name' => 'resit-kertas.png',
        ]);
        $this->assertSame('2026-09-05', $expense->purchase_date->toDateString());
        $this->assertTrue(Storage::disk('local')->exists($expense->receipt_path));

        $this->actingAs($admin)
            ->get(route('admin.expenses.receipt.download', $expense))
            ->assertDownload('resit-kertas.png');

        $this->actingAs($admin)
            ->get(route('admin.expenses.receipt.preview', $expense))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_admin_can_store_expense_with_pdf_receipt(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = ExpenseCategory::query()->create(['name' => 'Langganan']);
        $receipt = UploadedFile::fake()->create('resit-bulanan.pdf', 20, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'expense_category_id' => $category->id,
            'description' => 'Langganan internet',
            'amount' => '89.00',
            'purchase_date' => '2026-09-05',
            'receipt' => $receipt,
        ]);

        $response->assertRedirect(route('admin.expenses.index'));
        $expense = Expense::query()->firstOrFail();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'receipt_original_name' => 'resit-bulanan.pdf',
            'receipt_mime_type' => 'application/pdf',
        ]);
        $this->assertTrue(Storage::disk('local')->exists($expense->receipt_path));

        $this->actingAs($admin)
            ->get(route('admin.expenses.receipt.download', $expense))
            ->assertDownload('resit-bulanan.pdf');

        $this->actingAs($admin)
            ->get(route('admin.expenses.receipt.preview', $expense))
            ->assertNotFound();
    }

    public function test_admin_can_update_expense_details_without_replacing_receipt(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $oldCategory = ExpenseCategory::query()->create(['name' => 'Pejabat']);
        $newCategory = ExpenseCategory::query()->create(['name' => 'Operasi']);
        $receiptPath = 'expenses/receipts/original.png';
        Storage::disk('local')->put($receiptPath, 'receipt');
        $expense = Expense::query()->create([
            'created_by' => $admin->id,
            'expense_category_id' => $oldCategory->id,
            'description' => 'Pembelian lama',
            'amount' => 12.00,
            'purchase_date' => '2026-09-01',
            'notes' => 'Nota lama',
            'receipt_path' => $receiptPath,
            'receipt_original_name' => 'original.png',
            'receipt_mime_type' => 'image/png',
            'receipt_file_size' => 7,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.expenses.update', $expense), [
            'expense_category_id' => $newCategory->id,
            'description' => 'Pembelian dikemaskini',
            'amount' => '24.50',
            'purchase_date' => '2026-09-08',
            'notes' => 'Nota baharu',
        ]);

        $response->assertRedirect(route('admin.expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'expense_category_id' => $newCategory->id,
            'description' => 'Pembelian dikemaskini',
            'amount' => '24.50',
            'notes' => 'Nota baharu',
            'receipt_path' => $receiptPath,
        ]);
        $this->assertTrue(Storage::disk('local')->exists($receiptPath));
    }

    public function test_admin_can_replace_receipt_when_updating_expense(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = ExpenseCategory::query()->create(['name' => 'Langganan']);
        $oldReceiptPath = 'expenses/receipts/old.pdf';
        Storage::disk('local')->put($oldReceiptPath, 'old receipt');
        $expense = Expense::query()->create([
            'created_by' => $admin->id,
            'expense_category_id' => $category->id,
            'description' => 'Langganan lama',
            'amount' => 35.00,
            'purchase_date' => '2026-09-01',
            'receipt_path' => $oldReceiptPath,
            'receipt_original_name' => 'old.pdf',
            'receipt_mime_type' => 'application/pdf',
            'receipt_file_size' => 11,
        ]);
        $newReceipt = UploadedFile::fake()->image('resit-baharu.png', 120, 80);

        $response = $this->actingAs($admin)->post(route('admin.expenses.update', $expense), [
            '_method' => 'PUT',
            'expense_category_id' => $category->id,
            'description' => 'Langganan baharu',
            'amount' => '40.00',
            'purchase_date' => '2026-09-09',
            'receipt' => $newReceipt,
        ]);

        $response->assertRedirect(route('admin.expenses.index'));
        $expense->refresh();

        $this->assertNotSame($oldReceiptPath, $expense->receipt_path);
        $this->assertSame('resit-baharu.png', $expense->receipt_original_name);
        $this->assertSame('image/png', $expense->receipt_mime_type);
        $this->assertFalse(Storage::disk('local')->exists($oldReceiptPath));
        $this->assertTrue(Storage::disk('local')->exists($expense->receipt_path));
    }

    public function test_expense_rejects_unsupported_receipt(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = ExpenseCategory::query()->create(['name' => 'Lain-lain']);

        $response = $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'expense_category_id' => $category->id,
            'description' => 'Fail tidak sah',
            'amount' => '10.00',
            'purchase_date' => '2026-09-05',
            'receipt' => UploadedFile::fake()->create('resit.txt', 20, 'text/plain'),
        ]);

        $response->assertSessionHasErrors('receipt');
        $this->assertDatabaseCount('expenses', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('expenses/receipts'));
    }

    public function test_non_admin_cannot_access_expenses_or_receipt(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['is_admin' => false]);
        $path = 'expenses/receipts/private.png';
        Storage::disk('local')->put($path, 'private receipt');
        $expense = Expense::query()->create([
            'description' => 'Rekod sulit',
            'amount' => 12.00,
            'purchase_date' => '2026-09-05',
            'receipt_path' => $path,
            'receipt_original_name' => 'private.png',
            'receipt_mime_type' => 'image/png',
            'receipt_file_size' => 15,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.expenses.index'))
            ->assertRedirect(route('admin.login'));
        $this->actingAs($customer)
            ->get(route('admin.expenses.receipt.download', $expense))
            ->assertRedirect(route('admin.login'));
        $this->actingAs($customer)
            ->get(route('admin.expenses.receipt.preview', $expense))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_delete_expense_and_private_receipt(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $path = 'expenses/receipts/delete-me.png';
        Storage::disk('local')->put($path, 'receipt');
        $expense = Expense::query()->create([
            'created_by' => $admin->id,
            'description' => 'Resit untuk dipadam',
            'amount' => 5.00,
            'purchase_date' => '2026-09-05',
            'receipt_path' => $path,
            'receipt_original_name' => 'delete-me.png',
            'receipt_mime_type' => 'image/png',
            'receipt_file_size' => 7,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.expenses.destroy', $expense));

        $response->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        $this->assertFalse(Storage::disk('local')->exists($path));
    }
}
