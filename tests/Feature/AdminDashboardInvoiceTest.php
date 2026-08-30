<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
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
            ->where('adminNotifications.0.key', 'invoices-pending')
            ->where('adminNotifications.0.count', 1)
        );
    }

    public function test_admin_dashboard_shows_default_address_statistics_by_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $selangorCustomer = User::factory()->create(['is_admin' => false]);
        $anotherSelangorCustomer = User::factory()->create(['is_admin' => false]);
        $pahangCustomer = User::factory()->create(['is_admin' => false]);
        $unknownStateCustomer = User::factory()->create(['is_admin' => false]);
        $nonDefaultCustomer = User::factory()->create(['is_admin' => false]);

        CustomerAddress::query()->create([
            'user_id' => $selangorCustomer->id,
            'recipient_name' => 'Penerima Selangor Pertama',
            'address' => 'Jalan Damai, 43000 Kajang, Selangor',
            'no_hp' => null,
            'is_default' => true,
        ]);
        CustomerAddress::query()->create([
            'user_id' => $anotherSelangorCustomer->id,
            'recipient_name' => 'Penerima Selangor Kedua',
            'address' => 'Jalan Harmoni, 40150 Shah Alam, SELANGOR',
            'no_hp' => null,
            'is_default' => true,
        ]);
        CustomerAddress::query()->create([
            'user_id' => $pahangCustomer->id,
            'recipient_name' => 'Penerima Pahang',
            'address' => 'Jalan Kuantan, 25000 Kuantan, Pahang',
            'no_hp' => null,
            'is_default' => true,
        ]);
        CustomerAddress::query()->create([
            'user_id' => $unknownStateCustomer->id,
            'recipient_name' => 'Penerima Tanpa Negeri',
            'address' => 'Alamat Tanpa Negeri',
            'no_hp' => null,
            'is_default' => true,
        ]);
        CustomerAddress::query()->create([
            'user_id' => $nonDefaultCustomer->id,
            'recipient_name' => 'Penerima Bukan Default',
            'address' => 'Jalan Johor, 80000 Johor Bahru, Johor',
            'no_hp' => null,
            'is_default' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('addressStatistics.total_default_addresses', 4)
            ->where('addressStatistics.classified_addresses', 3)
            ->where('addressStatistics.unclassified_addresses', 1)
            ->has('addressStatistics.states', 2)
            ->where('addressStatistics.states.0.state', 'Selangor')
            ->where('addressStatistics.states.0.count', 2)
            ->where('addressStatistics.states.1.state', 'Pahang')
            ->where('addressStatistics.states.1.count', 1)
        );
    }
}
