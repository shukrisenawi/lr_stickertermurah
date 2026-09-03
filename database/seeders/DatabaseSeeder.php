<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PriceSetting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@sticker'],
            [
                'name' => 'admin@sticker',
                'password' => '123',
                'is_admin' => true,
            ]
        );

        $catA = Category::query()->updateOrCreate(
            ['slug' => 'label-produk'],
            ['name' => 'Label Produk', 'prefix' => 'LP', 'is_active' => true]
        );
        $catB = Category::query()->updateOrCreate(
            ['slug' => 'logo-perniagaan'],
            ['name' => 'Logo Perniagaan', 'prefix' => 'LG', 'is_active' => true]
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
            ['cm' => 2.5, 'name' => '2.5cm x 2.5cm', 'price' => 30 / 100, 'default' => false],
            ['cm' => 3, 'name' => '3cm x 3cm', 'price' => 30 / 300, 'default' => false],
            ['cm' => 4, 'name' => '4cm x 4cm', 'price' => 30 / 200, 'default' => false],
            ['cm' => 5, 'name' => '5cm x 5cm', 'price' => 30 / 100, 'default' => true],
            ['cm' => 6, 'name' => '6cm x 6cm', 'price' => 40 / 100, 'default' => false],
            ['cm' => 7, 'name' => '7cm x 7cm', 'price' => 50 / 100, 'default' => false],
            ['cm' => 8, 'name' => '8cm x 8cm', 'price' => 70 / 100, 'default' => false],
            ['cm' => 9, 'name' => '9cm x 9cm', 'price' => 90 / 100, 'default' => false],
            ['cm' => 10, 'name' => '10cm x 10cm', 'price' => 100 / 100, 'default' => false],
            ['cm' => 11, 'name' => '11cm x 11cm', 'price' => 110 / 100, 'default' => false],
            ['cm' => 12, 'name' => '12cm x 12cm', 'price' => 135 / 100, 'default' => false],
            ['cm' => 13, 'name' => '13cm x 13cm', 'price' => 155 / 100, 'default' => false],
            ['cm' => 14, 'name' => '14cm x 14cm', 'price' => 175 / 100, 'default' => false],
        ];

        foreach ($sizes as $s) {
            StickerSize::query()->updateOrCreate(
                ['name' => $s['name']],
                [
                    'width_cm' => $s['cm'],
                    'height_cm' => $s['cm'],
                    'shape' => 'Segi Empat Sama',
                    'qty_per_a3' => max(1, (int) (floor(42 / $s['cm']) * floor(29.7 / $s['cm']))),
                    'price' => round($s['price'], 4),
                    'is_default' => $s['default'],
                    'is_active' => true,
                    'show' => true,
                ]
            );
        }

        PriceSetting::query()->where('sticker_type', 'Mirrorcote')->delete();

        $mirrorcoteTiers = [
            ['qty_from' => 1, 'qty_to' => 5, 'price_per_a3' => 10.00],
            ['qty_from' => 6, 'qty_to' => 15, 'price_per_a3' => 8.00],
            ['qty_from' => 16, 'qty_to' => 30, 'price_per_a3' => 7.00],
            ['qty_from' => 31, 'qty_to' => 50, 'price_per_a3' => 6.00],
            ['qty_from' => 51, 'qty_to' => 100, 'price_per_a3' => 5.00],
            ['qty_from' => 101, 'qty_to' => null, 'price_per_a3' => 4.00],
        ];

        foreach ($mirrorcoteTiers as $tier) {
            PriceSetting::query()->create([
                'sticker_type' => 'Mirrorcote',
                'qty_from' => $tier['qty_from'],
                'qty_to' => $tier['qty_to'],
                'price_per_a3' => $tier['price_per_a3'],
                'is_active' => true,
            ]);
        }
    }
}
