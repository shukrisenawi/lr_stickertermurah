<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sticker_price_tiers');

        Schema::table('sticker_sizes', function (Blueprint $table) {
            $table->string('shape')->nullable()->after('price')->comment('petak, bulat, segi empat, dll');
            $table->unsignedInteger('qty_per_a3')->nullable()->after('shape')->comment('kuantiti sticker per A3 sheet');
        });

        Schema::create('price_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sticker_type')->default('Mirrorcote');
            $table->unsignedInteger('qty_from')->default(1);
            $table->unsignedInteger('qty_to')->nullable();
            $table->decimal('price_per_a3', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_settings');

        Schema::table('sticker_sizes', function (Blueprint $table) {
            $table->dropColumn(['shape', 'qty_per_a3']);
        });

        Schema::create('sticker_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sticker_size_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }
};
