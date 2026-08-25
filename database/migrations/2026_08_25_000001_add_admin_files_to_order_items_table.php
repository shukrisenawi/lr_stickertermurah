<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('admin_source_path')->nullable()->after('customer_design_paths');
            $table->string('customer_preview_path')->nullable()->after('admin_source_path');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['admin_source_path', 'customer_preview_path']);
        });
    }
};
