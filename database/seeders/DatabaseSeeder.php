<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@sticker.com'],
            [
                'name' => 'Admin Sticker',
                'password' => 'password',
                'is_admin' => true,
            ]
        );

        $catA = Category::query()->updateOrCreate(
            ['slug' => 'label-produk'],
            ['name' => 'Label Produk', 'is_active' => true]
        );
        $catB = Category::query()->updateOrCreate(
            ['slug' => 'logo-perniagaan'],
            ['name' => 'Logo Perniagaan', 'is_active' => true]
        );

        StickerDesign::query()->updateOrCreate(
            ['name' => 'Classic Label'],
            ['category_id' => $catA->id, 'description' => 'Design label simple', 'is_active' => true]
        );
        StickerDesign::query()->updateOrCreate(
            ['name' => 'Bold Logo'],
            ['category_id' => $catB->id, 'description' => 'Design logo branding', 'is_active' => true]
        );

        $sizes = [
            ['name' => '2cm x 2cm', 'width_cm' => 2, 'height_cm' => 2, 'price' => 0.50, 'is_default' => true],
            ['name' => '3cm x 3cm', 'width_cm' => 3, 'height_cm' => 3, 'price' => 0.80, 'is_default' => false],
            ['name' => '5cm x 5cm', 'width_cm' => 5, 'height_cm' => 5, 'price' => 1.20, 'is_default' => false],
        ];

        foreach ($sizes as $size) {
            StickerSize::query()->updateOrCreate(
                ['name' => $size['name']],
                [
                    'width_cm' => $size['width_cm'],
                    'height_cm' => $size['height_cm'],
                    'price' => $size['price'],
                    'is_default' => $size['is_default'],
                    'is_active' => true,
                ]
            );
        }
    }
}
