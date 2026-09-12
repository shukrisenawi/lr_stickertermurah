<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bank_account_id',
    'uploaded_by',
    'year',
    'month',
    'file_path',
    'original_name',
    'mime_type',
    'file_size',
])]
class BankStatement extends Model
{
    public const MAX_FILE_SIZE_KB = 20480;

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'file_size' => 'integer',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
