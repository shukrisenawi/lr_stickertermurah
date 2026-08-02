<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'order_id', 'title', 'preview_path', 'preview_paths', 'source_path', 'source_paths', 'notes'])]
class CustomerProject extends Model
{
    protected function casts(): array
    {
        return [
            'preview_paths' => 'array',
            'source_paths' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
