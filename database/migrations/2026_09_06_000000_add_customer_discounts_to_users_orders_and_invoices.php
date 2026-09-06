<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('discount_amount', 12, 2)->default(0)->after('is_admin');
            $table->boolean('discount_forever')->default(false)->after('discount_amount');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('discount_amount', 12, 2)->default(0)->after('total');
            $table->boolean('discount_forever')->default(false)->after('discount_amount');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->decimal('discount_amount', 12, 2)->default(0)->after('amount');
            $table->boolean('discount_forever')->default(false)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['discount_amount', 'discount_forever']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['discount_amount', 'discount_forever']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['discount_amount', 'discount_forever']);
        });
    }
};
