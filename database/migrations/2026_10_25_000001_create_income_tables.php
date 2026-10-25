<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('incomes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('income_category_id')->nullable()->constrained('income_categories')->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('received_date')->index();
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime_type', 255)->nullable();
            $table->unsignedBigInteger('receipt_file_size')->default(0);
            $table->timestamps();
            $table->index(['received_date', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('income_categories');
    }
};
