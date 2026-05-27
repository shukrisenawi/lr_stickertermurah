<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('cut_type')->default('standard')->after('quantity')->comment('standard or die-cut');
            $table->string('customer_design_path')->nullable()->after('cut_type');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['cut_type', 'customer_design_path']);
        });
    }
};
