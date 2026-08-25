<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('admin_source_paths')->nullable()->after('admin_source_path');
            $table->json('customer_preview_paths')->nullable()->after('customer_preview_path');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['admin_source_paths', 'customer_preview_paths']);
        });
    }
};
