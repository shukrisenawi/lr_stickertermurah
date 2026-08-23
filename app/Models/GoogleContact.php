<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'google_contact_connection_id',
    'resource_name',
    'etag',
    'name',
    'normalized_phone',
    'phone',
    'email',
    'address',
])]
class GoogleContact extends Model
{
    public function googleContactConnection(): BelongsTo
    {
        return $this->belongsTo(GoogleContactConnection::class);
    }
}
