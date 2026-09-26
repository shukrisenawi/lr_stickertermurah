<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->decimal('shipping_cost', 10, 2)->default(5.60)->after('deposit_amount');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->decimal('shipping_cost', 10, 2)->default(5.60)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn('shipping_cost');
        });

        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->dropColumn('shipping_cost');
        });
    }
};
