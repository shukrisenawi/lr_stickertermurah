<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'sticker_design_id', 'sticker_size_id', 'min_qty', 'max_qty', 'type', 'value', 'is_active'])]
class Discount extends Model
{
    protected function casts(): array
    {
        return [
            'min_qty' => 'integer',
            'max_qty' => 'integer',
            'value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(StickerDesign::class, 'sticker_design_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(StickerSize::class, 'sticker_size_id');
    }
}
