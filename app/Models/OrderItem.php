<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'sticker_design_id',
    'custom_design_description',
    'sticker_size_id',
    'requested_size',
    'quantity',
    'cut_type',
    'customer_design_path',
    'unit_price',
    'line_total',
])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
