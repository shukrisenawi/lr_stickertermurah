<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'account_number', 'account_holder', 'previous_year_balance'])]
class BankAccount extends Model
{
    protected function casts(): array
    {
        return [
            'previous_year_balance' => 'decimal:2',
        ];
    }

    public function monthlyRecords(): HasMany
    {
        return $this->hasMany(BankMonthlyRecord::class);
    }
}
