<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('deposit_amount', 10, 2)->nullable()->after('total');
            $table->decimal('balance_due', 10, 2)->nullable()->after('deposit_amount');
            $table->string('payment_status')->default('pending')->after('balance_due');
            $table->boolean('design_confirmed')->default(false)->after('payment_status');
            $table->string('design_proof_path')->nullable()->after('design_confirmed');
            $table->string('custom_description')->nullable()->after('custom_request');
            $table->string('payment_type')->nullable()->after('payment_status')->comment('deposit or full');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('sticker_design_id')->nullable()->change();
            $table->string('custom_design_description')->nullable()->after('sticker_design_id');
            $table->string('requested_size')->nullable()->after('sticker_size_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'balance_due', 'payment_status', 'design_confirmed', 'design_proof_path', 'custom_description', 'payment_type']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['custom_design_description', 'requested_size']);
        });
    }
};
