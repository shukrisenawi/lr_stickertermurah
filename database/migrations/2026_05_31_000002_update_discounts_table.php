<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropForeign(['sticker_design_id']);
            $table->dropColumn('sticker_design_id');
            $table->string('sticker_type')->nullable()->after('name');
            $table->date('expired_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['sticker_type', 'expired_at']);
            $table->foreignId('sticker_design_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
