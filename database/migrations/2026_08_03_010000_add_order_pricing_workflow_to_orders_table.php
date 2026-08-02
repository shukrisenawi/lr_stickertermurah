<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pricing_status')->default('pending_admin')->after('total');
            $table->text('price_note')->nullable()->after('pricing_status');
            $table->timestamp('price_quoted_at')->nullable()->after('price_note');
            $table->timestamp('price_approved_at')->nullable()->after('price_quoted_at');
        });

        // Kekalkan order lama yang sudah mempunyai jumlah harga dan boleh diinvois seperti biasa.
        DB::table('orders')->where('total', '>', 0)->update(['pricing_status' => 'auto_priced']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pricing_status', 'price_note', 'price_quoted_at', 'price_approved_at']);
        });
    }
};
