<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'width_cm', 'height_cm', 'shape', 'qty_per_a3', 'price', 'is_default', 'is_active'])]
class StickerSize extends Model
{
    protected function casts(): array
    {
        return [
            'width_cm' => 'float',
            'height_cm' => 'float',
            'qty_per_a3' => 'integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
