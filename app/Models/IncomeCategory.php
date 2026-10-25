<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class IncomeCategory extends Model
{
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }
}
