<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_database_backup_as_sql(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.database.backup'));
        $content = $response->streamedContent();

        $response->assertOk();
        $response->assertDownload();
        $response->assertHeader('Content-Type', 'application/sql');
        $this->assertStringContainsString('PRAGMA foreign_keys=OFF;', $content);
        $this->assertStringContainsString('CREATE TABLE', $content);
        $this->assertStringContainsString('INSERT INTO "users"', $content);
    }

    public function test_non_admin_cannot_download_database_backup(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('admin.database.backup'))
            ->assertRedirect(route('admin.login'));
    }
}
