<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'business', 'text', 'image_path', 'stars', 'is_approved', 'approved_at', 'approved_by', 'user_id'])]
class Testimonial extends Model
{
    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'stars' => 'integer',
    ];

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
