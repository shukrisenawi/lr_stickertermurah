<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('purchase_date')->index();
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime_type', 255)->nullable();
            $table->unsignedBigInteger('receipt_file_size')->default(0);
            $table->timestamps();

            $table->index(['purchase_date', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
