<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->decimal('previous_year_balance', 14, 2)->default(0)->after('account_holder');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->dropColumn('previous_year_balance');
        });
    }
};
