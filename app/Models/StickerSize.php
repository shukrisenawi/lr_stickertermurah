<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'width_cm', 'height_cm', 'price', 'is_default', 'is_active'])]
class StickerSize extends Model
{
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(StickerPriceTier::class);
    }
}
