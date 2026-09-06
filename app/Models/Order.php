<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'customer_address_id',
    'order_no',
    'customer_name',
    'customer_phone',
    'customer_address',
    'material',
    'status',
    'tracking_no',
    'custom_request',
    'custom_description',
    'payment_receipt_path',
    'subtotal',
    'total',
    'discount_amount',
    'discount_forever',
    'shipping_region',
    'shipping_fee',
    'shipping_free',
    'shipping_free_forever',
    'pricing_status',
    'price_note',
    'price_quoted_at',
    'price_approved_at',
    'deposit_amount',
    'balance_due',
    'payment_status',
    'payment_type',
    'design_confirmed',
    'design_proof_path',
    'repeat_from_order_id',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_forever' => 'boolean',
            'shipping_fee' => 'decimal:2',
            'shipping_free' => 'boolean',
            'shipping_free_forever' => 'boolean',
            'price_quoted_at' => 'datetime',
            'price_approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (! $order->order_no) {
                $order->order_no = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
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

    public function customerTrackingNo(): ?string
    {
        if ($this->status !== 'completed') {
            return null;
        }

        $trackingNo = trim((string) ($this->invoice?->tracking_no ?: $this->tracking_no));

        return $trackingNo !== '' ? $trackingNo : null;
    }

    public function repeatFrom(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'repeat_from_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customerAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class);
    }

    public function customerProjects(): HasMany
    {
        return $this->hasMany(CustomerProject::class);
    }
}
