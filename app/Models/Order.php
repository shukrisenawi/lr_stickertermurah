<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'order_no',
    'customer_name',
    'customer_phone',
    'customer_address',
    'material',
    'status',
    'tracking_no',
    'custom_request',
    'payment_receipt_path',
    'subtotal',
    'total',
    'repeat_from_order_id',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (! $order->order_no) {
                $order->order_no = 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function repeatFrom(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'repeat_from_order_id');
    }
}
