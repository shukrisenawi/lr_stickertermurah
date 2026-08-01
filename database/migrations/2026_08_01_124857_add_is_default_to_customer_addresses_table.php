<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('no_hp');
        });

        // Set alamat pertama setiap user sebagai default
        DB::statement('
            UPDATE customer_addresses SET is_default = 1
            WHERE id IN (
                SELECT MIN(id) FROM customer_addresses GROUP BY user_id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};