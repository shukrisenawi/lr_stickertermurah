<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bank_account_id',
    'year',
    'month',
    'opening_balance',
    'income',
    'expense',
    'notes',
])]
class BankMonthlyRecord extends Model
{
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'opening_balance' => 'decimal:2',
            'income' => 'decimal:2',
            'expense' => 'decimal:2',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function calculatedClosingBalance(): float
    {
        return round((float) $this->opening_balance + (float) $this->income - (float) $this->expense, 2);
    }
}
