<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'customer_addresses')
            ->where('index_name', 'customer_addresses_user_id_address_unique')
            ->exists();

        if ($indexExists) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->unique(['user_id', 'address'], 'customer_addresses_user_id_address_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'customer_addresses')
            ->where('index_name', 'customer_addresses_user_id_address_unique')
            ->exists();

        if (! $indexExists) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropUnique('customer_addresses_user_id_address_unique');
        });
    }
};
