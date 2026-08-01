<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id',
    'user_id',
    'invoice_no',
    'issue_date',
    'amount',
    'notes',
    'customer_name',
    'customer_phone',
    'customer_address',
    'payment_status',
    'payment_type',
    'payment_amount',
    'payment_method',
    'payment_receipt_path',
    'paid_at',
    'payment_submitted_at',
    'approved_by',
    'payment_note',
])]
class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'amount' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}