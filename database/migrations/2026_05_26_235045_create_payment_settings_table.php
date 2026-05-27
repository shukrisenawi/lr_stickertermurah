<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->default('Bank Islam');
            $table->string('bank_account_no')->default('123124');
            $table->string('bank_account_name')->default('SH BEST CREATIVE DESIGN');
            $table->string('qr_image_path')->nullable();
            $table->string('admin_phone')->default('011-69409606');
            $table->string('admin_email')->default('stickertermurah@gmail.com');
            $table->decimal('deposit_amount', 10, 2)->default(20.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
