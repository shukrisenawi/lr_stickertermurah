<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'created_by', 'income_category_id', 'description', 'amount', 'received_date', 'notes',
    'receipt_path', 'receipt_original_name', 'receipt_mime_type', 'receipt_file_size',
])]
class Income extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'received_date' => 'date', 'receipt_file_size' => 'integer'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id');
    }
}
