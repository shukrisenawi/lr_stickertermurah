<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['bank_name', 'bank_account_no', 'bank_account_name', 'bank_logo_path', 'qr_image_path', 'admin_phone', 'admin_email', 'deposit_amount'])]
class PaymentSetting extends Model
{
    protected $casts = [
        'deposit_amount' => 'decimal:2',
    ];
}
