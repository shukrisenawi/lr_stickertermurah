<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_name', 'company_address', 'company_phone', 'company_logo_path', 'bank_name', 'bank_account_no', 'bank_account_name', 'bank_logo_path', 'qr_image_path', 'admin_phone', 'admin_email', 'deposit_amount', 'shipping_cost'])]
class PaymentSetting extends Model
{
    public const DEFAULT_SHIPPING_COST = 5.60;

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    public static function defaultShippingCost(): float
    {
        return round((float) (static::query()->value('shipping_cost') ?? self::DEFAULT_SHIPPING_COST), 2);
    }
}
