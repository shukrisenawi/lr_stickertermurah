<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_projects', function (Blueprint $table) {
            $table->json('source_paths')->nullable()->after('source_path');
        });
    }

    public function down(): void
    {
        Schema::table('customer_projects', function (Blueprint $table) {
            $table->dropColumn('source_paths');
        });
    }
};
