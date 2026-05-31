<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('sticker_design_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sticker_size_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('min_qty')->default(1);
            $table->unsignedInteger('max_qty')->nullable();
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
