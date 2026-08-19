<?php

namespace Tests\Feature;

use App\Models\CompanyDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCompanyDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_company_documents_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.company-documents.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CompanyDocuments/Index')
            ->has('documents.data', 0)
            ->where('filters.search', '')
            ->where('filters.category', '')
            ->where('maxFileSizeMb', 20)
            ->has('categories', 7)
        );
    }

    public function test_admin_can_upload_and_download_a_company_document(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $file = UploadedFile::fake()->create('ssm-2026.pdf', 120, 'application/pdf');

        $storeResponse = $this->actingAs($admin)->post(route('admin.company-documents.store'), [
            'title' => 'SSM Syarikat 2026',
            'category' => 'ssm',
            'notes' => 'Dokumen pendaftaran syarikat.',
            'files' => [$file],
        ]);

        $storeResponse->assertRedirect(route('admin.company-documents.index'));
        $document = CompanyDocument::query()->firstOrFail();

        $this->assertDatabaseHas('company_documents', [
            'id' => $document->id,
            'uploaded_by' => $admin->id,
            'title' => 'SSM Syarikat 2026',
            'category' => 'ssm',
            'original_name' => 'ssm-2026.pdf',
        ]);
        $this->assertTrue(Storage::disk('local')->exists($document->file_path));

        $downloadResponse = $this->actingAs($admin)->get(route('admin.company-documents.download', $document));

        $downloadResponse->assertDownload('ssm-2026.pdf');
    }

    public function test_admin_can_upload_multiple_documents_using_each_file_name_as_title(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $files = [
            UploadedFile::fake()->create('ssm-2026.pdf', 120, 'application/pdf'),
            UploadedFile::fake()->create('resit-januari.pdf', 80, 'application/pdf'),
        ];

        $response = $this->actingAs($admin)->post(route('admin.company-documents.store'), [
            'category' => 'other',
            'files' => $files,
        ]);

        $response->assertRedirect(route('admin.company-documents.index'));
        $this->assertDatabaseCount('company_documents', 2);
        $this->assertDatabaseHas('company_documents', ['title' => 'ssm-2026.pdf', 'original_name' => 'ssm-2026.pdf']);
        $this->assertDatabaseHas('company_documents', ['title' => 'resit-januari.pdf', 'original_name' => 'resit-januari.pdf']);
    }

    public function test_single_upload_uses_original_file_name_when_title_is_empty(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.company-documents.store'), [
            'title' => '',
            'category' => 'ssm',
            'files' => [UploadedFile::fake()->create('pendaftaran-ssm.pdf', 80, 'application/pdf')],
        ]);

        $response->assertRedirect(route('admin.company-documents.index'));
        $this->assertDatabaseHas('company_documents', [
            'title' => 'pendaftaran-ssm.pdf',
            'original_name' => 'pendaftaran-ssm.pdf',
        ]);
    }

    public function test_upload_rejects_unsupported_company_document_type(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.company-documents.store'), [
            'title' => 'Fail Tidak Sah',
            'category' => 'other',
            'files' => [UploadedFile::fake()->create('malicious.php', 10, 'application/x-php')],
        ]);

        $response->assertSessionHasErrors('files.0');
        $this->assertDatabaseCount('company_documents', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('company-documents'));
    }

    public function test_non_admin_cannot_access_or_download_company_documents(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['is_admin' => false]);
        $document = CompanyDocument::query()->create([
            'title' => 'Dokumen Sulit',
            'category' => 'ssm',
            'file_path' => 'company-documents/rahsia.pdf',
            'original_name' => 'rahsia.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 10,
        ]);

        Storage::disk('local')->put($document->file_path, 'private content');

        $this->actingAs($customer)
            ->get(route('admin.company-documents.index'))
            ->assertRedirect(route('admin.login'));
        $this->actingAs($customer)
            ->get(route('admin.company-documents.download', $document))
            ->assertRedirect(route('admin.login'));
    }

    public function test_deleting_company_document_removes_database_record_and_private_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $path = 'company-documents/receipt.pdf';
        Storage::disk('local')->put($path, 'receipt');
        $document = CompanyDocument::query()->create([
            'uploaded_by' => $admin->id,
            'title' => 'Resit Pembelian',
            'category' => 'receipt',
            'file_path' => $path,
            'original_name' => 'receipt.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 7,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.company-documents.destroy', $document));

        $response->assertRedirect();
        $this->assertDatabaseMissing('company_documents', ['id' => $document->id]);
        $this->assertFalse(Storage::disk('local')->exists($path));
    }
}
