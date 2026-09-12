<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\BankMonthlyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminBankAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_current_year_bank_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.bank-accounts.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/BankAccounts/Index')
            ->has('banks', 0)
            ->where('year', now()->year)
            ->where('currentYear', now()->year)
            ->where('currentMonth', now()->month)
            ->where('totals.bank_count', 0)
            ->where('totals.current_balance', 0)
            ->has('years', 1)
        );
    }

    public function test_admin_can_add_multiple_banks_and_save_monthly_record(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.bank-accounts.store'), [
            'name' => 'Maybank',
            'account_number' => '514200991234',
            'account_holder' => 'SH Best Creative Design',
        ])->assertRedirect(route('admin.bank-accounts.index'));

        $cimb = BankAccount::query()->create(['name' => 'CIMB']);
        $maybank = BankAccount::query()->where('name', 'Maybank')->firstOrFail();
        $year = now()->year;

        $this->actingAs($admin)->post(route('admin.bank-accounts.records.store'), [
            'bank_account_id' => $cimb->id,
            'year' => $year,
            'month' => 1,
            'opening_balance' => '1000.00',
            'income' => '130.00',
            'expense' => '50.00',
            'notes' => 'Penyata Januari',
        ])->assertRedirect(route('admin.bank-accounts.index', ['year' => $year]));

        $this->actingAs($admin)->post(route('admin.bank-accounts.records.store'), [
            'bank_account_id' => $cimb->id,
            'year' => $year,
            'month' => 1,
            'opening_balance' => '1000.00',
            'income' => '150.00',
            'expense' => '50.00',
        ])->assertRedirect(route('admin.bank-accounts.index', ['year' => $year]));

        $record = BankMonthlyRecord::query()->firstOrFail();
        $this->assertDatabaseCount('bank_accounts', 2);
        $this->assertDatabaseCount('bank_monthly_records', 1);
        $this->assertDatabaseHas('bank_monthly_records', [
            'id' => $record->id,
            'bank_account_id' => $cimb->id,
            'year' => $year,
            'month' => 1,
            'income' => '150.00',
            'expense' => '50.00',
        ]);

        $this->actingAs($admin)->put(route('admin.bank-accounts.records.update', $record), [
            'bank_account_id' => $cimb->id,
            'year' => $year,
            'month' => 1,
            'opening_balance' => '1000.00',
            'income' => '125.00',
            'expense' => '25.00',
        ])->assertRedirect(route('admin.bank-accounts.index', ['year' => $year]));

        $this->actingAs($admin)
            ->get(route('admin.bank-accounts.index', ['year' => $year]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/BankAccounts/Index')
                ->has('banks', 2)
                ->where('banks.0.name', 'CIMB')
                ->where('banks.0.records.0.income', 125)
                ->where('banks.0.records.0.closing_balance', 1100)
                ->where('banks.1.name', 'Maybank')
                ->where('totals.income', 125)
                ->where('totals.expense', 25)
                ->where('totals.current_balance', 1100)
            );

        $this->assertNotNull($maybank->fresh());
    }

    public function test_bank_with_monthly_records_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $bank = BankAccount::query()->create(['name' => 'Bank Rekod']);
        BankMonthlyRecord::query()->create([
            'bank_account_id' => $bank->id,
            'year' => now()->year,
            'month' => 1,
            'opening_balance' => 10,
            'income' => 5,
            'expense' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.bank-accounts.destroy', $bank))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bank_accounts', ['id' => $bank->id]);
    }

    public function test_non_admin_cannot_access_bank_accounts(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('admin.bank-accounts.index'))
            ->assertRedirect(route('admin.login'));
    }
}
