<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'sticker_design_id',
    'customer_project_id',
    'customer_project_source_index',
    'customer_project_source_indices',
    'customer_project_sources',
    'custom_design_description',
    'sticker_size_id',
    'requested_size',
    'quantity',
    'cut_type',
    'customer_design_path',
    'customer_design_paths',
    'admin_source_path',
    'customer_preview_path',
    'unit_price',
    'line_total',
])]
#[Hidden(['admin_source_path', 'customer_preview_path'])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'customer_project_source_indices' => 'array',
            'customer_project_sources' => 'array',
            'customer_design_paths' => 'array',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(CustomerProject::class, 'customer_project_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(StickerSize::class, 'sticker_size_id');
    }
}
