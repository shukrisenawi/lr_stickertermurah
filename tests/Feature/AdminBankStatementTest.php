<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminBankStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_download_a_pdf_bank_statement(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $bank = BankAccount::query()->create(['name' => 'Maybank']);
        $file = UploadedFile::fake()->create('maybank-januari.pdf', 120, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('admin.bank-accounts.statements.store', $bank), [
            'year' => 2026,
            'month' => 1,
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.bank-accounts.index', ['year' => 2026]));
        $statement = BankStatement::query()->firstOrFail();

        $this->assertDatabaseHas('bank_statements', [
            'id' => $statement->id,
            'bank_account_id' => $bank->id,
            'uploaded_by' => $admin->id,
            'year' => 2026,
            'month' => 1,
            'original_name' => 'maybank-januari.pdf',
        ]);
        $this->assertTrue(Storage::disk('local')->exists($statement->file_path));

        $this->actingAs($admin)
            ->get(route('admin.bank-accounts.index', ['year' => 2026]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/BankAccounts/Index')
                ->where('maxStatementSizeMb', 20)
                ->where('banks.0.statements.0.original_name', 'maybank-januari.pdf')
                ->where('banks.0.statements.0.preview_url', null)
            );

        $this->actingAs($admin)
            ->get(route('admin.bank-statements.download', $statement))
            ->assertDownload('maybank-januari.pdf');
    }

    public function test_admin_can_preview_an_image_bank_statement(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $bank = BankAccount::query()->create(['name' => 'CIMB']);
        $file = UploadedFile::fake()->image('cimb-februari.jpg', 120, 80);

        $this->actingAs($admin)->post(route('admin.bank-accounts.statements.store', $bank), [
            'year' => 2026,
            'month' => 2,
            'file' => $file,
        ])->assertRedirect();

        $statement = BankStatement::query()->firstOrFail();
        $response = $this->actingAs($admin)->get(route('admin.bank-statements.preview', $statement));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_bank_statement_rejects_unsupported_file_types(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $bank = BankAccount::query()->create(['name' => 'RHB']);

        $response = $this->actingAs($admin)->post(route('admin.bank-accounts.statements.store', $bank), [
            'year' => 2026,
            'month' => 3,
            'file' => UploadedFile::fake()->create('statement.php', 10, 'application/x-php'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('bank_statements', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('bank-statements'));
    }

    public function test_admin_can_delete_a_bank_statement_and_private_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $bank = BankAccount::query()->create(['name' => 'Hong Leong']);
        $file = UploadedFile::fake()->create('hlb-april.pdf', 100, 'application/pdf');

        $this->actingAs($admin)->post(route('admin.bank-accounts.statements.store', $bank), [
            'year' => 2026,
            'month' => 4,
            'file' => $file,
        ]);
        $statement = BankStatement::query()->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.bank-statements.destroy', $statement))
            ->assertRedirect(route('admin.bank-accounts.index', ['year' => 2026]));

        $this->assertDatabaseMissing('bank_statements', ['id' => $statement->id]);
        $this->assertFalse(Storage::disk('local')->exists($statement->file_path));
    }
}
