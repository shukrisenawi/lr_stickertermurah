<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sticker_sizes')
            ->where('shape', 'Segi Empat')
            ->update(['shape' => 'Segi Empat Sama']);
    }

    public function down(): void
    {
        DB::table('sticker_sizes')
            ->where('shape', 'Segi Empat Sama')
            ->update(['shape' => 'Segi Empat']);
    }
};
