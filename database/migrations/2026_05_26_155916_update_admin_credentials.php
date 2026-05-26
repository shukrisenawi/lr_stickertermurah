<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('users')
            ->whereIn('email', ['admin@sticker.com', 'admin@sticker'])
            ->first();

        if ($existing) {
            DB::table('users')
                ->where('id', $existing->id)
                ->update([
                    'email' => 'admin@sticker',
                    'name' => 'admin@sticker',
                    'password' => Hash::make('123'),
                    'is_admin' => true,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('users')->insert([
                'email' => 'admin@sticker',
                'name' => 'admin@sticker',
                'password' => Hash::make('123'),
                'is_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
