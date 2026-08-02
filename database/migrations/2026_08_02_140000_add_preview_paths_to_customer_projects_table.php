<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_projects', function (Blueprint $table) {
            $table->json('preview_paths')->nullable()->after('preview_path');
        });
    }

    public function down(): void
    {
        Schema::table('customer_projects', function (Blueprint $table) {
            $table->dropColumn('preview_paths');
        });
    }
};
