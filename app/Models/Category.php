<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'prefix', 'is_active'])]
class Category extends Model
{
    public function designs(): HasMany
    {
        return $this->hasMany(StickerDesign::class);
    }
}
