<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sticker_size_id', 'quantity', 'total_price'])]
class StickerPriceTier extends Model
{
    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
        ];
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(StickerSize::class, 'sticker_size_id');
    }
}
