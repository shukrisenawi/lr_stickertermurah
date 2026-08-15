<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_monthly_invoice_sales_and_latest_invoices(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $currentMonth = now()->startOfMonth();

        Invoice::query()->create([
            'invoice_no' => 'INV-DASHBOARD-OLD',
            'issue_date' => $currentMonth->copy()->subMonth()->addDay()->toDateString(),
            'amount' => 20,
            'customer_name' => 'Pelanggan Lama',
            'payment_status' => 'paid',
        ]);

        Invoice::query()->create([
            'invoice_no' => 'INV-DASHBOARD-NEW',
            'issue_date' => $currentMonth->copy()->addDay()->toDateString(),
            'amount' => 35,
            'customer_name' => 'Pelanggan Baru',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('recentInvoices.0.invoice_no', 'INV-DASHBOARD-NEW')
            ->where('salesStats.total_amount', 55)
            ->where('salesStats.total_invoices', 2)
            ->where('salesStats.months.10.amount', 20)
            ->where('salesStats.months.11.amount', 35)
        );
    }
}
