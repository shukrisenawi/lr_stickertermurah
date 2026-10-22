<?php

namespace Tests\Unit;

use App\Services\ShippingService;
use PHPUnit\Framework\TestCase;

class ShippingServiceTest extends TestCase
{
    public function test_pickup_is_preserved_and_always_has_no_shipping_charge(): void
    {
        $shipping = new ShippingService;

        $this->assertSame('pickup', $shipping->normalize('pickup'));
        $this->assertSame(0.0, $shipping->calculate(30, 'pickup'));
        $this->assertSame('Tanpa pos (ambil sendiri)', $shipping->description('pickup', 0));
    }

    public function test_default_remains_postage_with_existing_rates(): void
    {
        $shipping = new ShippingService;

        $this->assertSame('peninsular', $shipping->normalize(null));
        $this->assertSame(7.0, $shipping->calculate(30, null));
        $this->assertSame(12.0, $shipping->calculate(30, 'sabah_sarawak'));
        $this->assertSame(0.0, $shipping->calculate(150, 'peninsular'));
        $this->assertSame(0.0, $shipping->calculate(30, 'sabah_sarawak', true));
    }
}
