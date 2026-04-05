<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\StickerDesign;
use App\Models\StickerPriceTier;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        $quantityColumns = [100, 200, 300, 500, 800, 1000, 2000, 3000, 5000];

        // Harga diambil daripada gambar: "Sticker Biasa (Mirrorcote)"
        $priceMatrix = [
            '2.5' => [null, null, null, 30, 50, 60, 85, 125, 160],
            '3' => [null, null, 30, 40, 70, 80, 130, 170, 240],
            '4' => [null, 30, 50, 80, 96, 100, 175, 260, 360],
            '5' => [30, 50, 80, 105, 140, 150, 250, 375, 500],
            '6' => [40, 80, 90, 145, 175, 215, 360, 430, 715],
            '7' => [50, 90, 105, 150, 205, 250, 420, 500, 630],
            '8' => [70, 110, 140, 200, 270, 335, 535, 600, 835],
            '9' => [90, 135, 150, 220, 335, 400, 500, 750, 1040],
            '10' => [100, 150, 220, 315, 400, 500, 750, 940, 1250],
            '11' => [110, 160, 230, 330, 420, 520, 770, 960, 1270],
            '12' => [135, 200, 250, 420, 530, 660, 835, 1000, 1600],
            '13' => [155, 220, 270, 440, 550, 680, 855, 1020, 1620],
            '14' => [175, 240, 290, 460, 570, 700, 875, 1040, 1640],
        ];

        foreach ($priceMatrix as $sizeCm => $prices) {
            $label = rtrim(rtrim($sizeCm, '0'), '.');
            $name = $label . 'cm x ' . $label . 'cm';

            $firstAvailableIndex = collect($prices)->search(fn ($value) => $value !== null);
            $defaultTotalPrice = $prices[$firstAvailableIndex];
            $defaultQty = $quantityColumns[$firstAvailableIndex];
            $unitPrice = $defaultTotalPrice / $defaultQty;

            $size = StickerSize::query()->updateOrCreate(
                ['name' => $name],
                [
                    'width_cm' => (float) $sizeCm,
                    'height_cm' => (float) $sizeCm,
                    'price' => round($unitPrice, 4),
                    'is_default' => $label === '5',
                    'is_active' => true,
                ]
            );

            StickerPriceTier::query()->where('sticker_size_id', $size->id)->delete();

            foreach ($quantityColumns as $index => $qty) {
                $totalPrice = $prices[$index];

                if ($totalPrice === null) {
                    continue;
                }

                StickerPriceTier::query()->create([
                    'sticker_size_id' => $size->id,
                    'quantity' => $qty,
                    'total_price' => $totalPrice,
                ]);
            }
        }
    }
}
