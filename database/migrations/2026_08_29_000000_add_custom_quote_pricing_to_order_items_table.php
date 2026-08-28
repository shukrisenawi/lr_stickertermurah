<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('quoted_qty_per_a3')->nullable()->after('line_total');
            $table->decimal('quoted_price_per_a3', 10, 2)->nullable()->after('quoted_qty_per_a3');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['quoted_qty_per_a3', 'quoted_price_per_a3']);
        });
    }
};
