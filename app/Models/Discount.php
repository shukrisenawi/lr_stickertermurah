<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'sticker_type', 'sticker_size_id', 'min_qty', 'max_qty', 'type', 'value', 'is_active', 'expired_at'])]
class Discount extends Model
{
    protected function casts(): array
    {
        return [
            'min_qty' => 'integer',
            'max_qty' => 'integer',
            'value' => 'decimal:2',
            'is_active' => 'boolean',
            'expired_at' => 'date',
        ];
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(StickerSize::class, 'sticker_size_id');
    }
}
