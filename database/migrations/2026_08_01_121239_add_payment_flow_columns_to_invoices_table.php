<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('amount')->comment('unpaid, submitted, paid, rejected');
            $table->string('payment_type')->nullable()->after('payment_status')->comment('deposit or full');
            $table->string('payment_method')->nullable()->after('payment_type');
            $table->string('payment_receipt_path')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_receipt_path');
            $table->timestamp('payment_submitted_at')->nullable()->after('paid_at');
            $table->foreignId('approved_by')->nullable()->after('payment_submitted_at')->constrained('users')->nullOnDelete();
            $table->text('payment_note')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'payment_status',
                'payment_type',
                'payment_method',
                'payment_receipt_path',
                'paid_at',
                'payment_submitted_at',
                'approved_by',
                'payment_note',
            ]);
        });
    }
};