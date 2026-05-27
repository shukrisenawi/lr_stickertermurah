<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sticker_type', 'qty_from', 'qty_to', 'price_per_a3', 'is_active'])]
class PriceSetting extends Model
{
    protected function casts(): array
    {
        return [
            'price_per_a3' => 'decimal:2',
            'qty_from' => 'integer',
            'qty_to' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
