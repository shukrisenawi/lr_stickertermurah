<?php

namespace App\Services;

class ShippingService
{
    public const PENINSULAR = 'peninsular';

    public const SABAH_SARAWAK = 'sabah_sarawak';

    public const PICKUP = 'pickup';

    public const FREE_SHIPPING_THRESHOLD = 150;

    public function calculate(float $subtotal, ?string $region, bool $free = false): float
    {
        if ($region === self::PICKUP || $free || $subtotal >= self::FREE_SHIPPING_THRESHOLD) {
            return 0.0;
        }

        return $region === self::SABAH_SARAWAK ? 12.0 : 7.0;
    }

    public function normalize(?string $region): string
    {
        if ($region === self::PICKUP) {
            return self::PICKUP;
        }

        return $region === self::SABAH_SARAWAK
            ? self::SABAH_SARAWAK
            : self::PENINSULAR;
    }

    public function description(?string $region, float $fee): string
    {
        if ($region === self::PICKUP) {
            return 'Tanpa pos (ambil sendiri)';
        }

        $area = $region === self::SABAH_SARAWAK
            ? 'Sabah & Sarawak'
            : 'Semenanjung Malaysia';

        return $fee > 0 ? "Pos - {$area}" : "Pos - {$area} (Percuma)";
    }
}
