<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'account_number', 'account_holder'])]
class BankAccount extends Model
{
    public function monthlyRecords(): HasMany
    {
        return $this->hasMany(BankMonthlyRecord::class);
    }
}
