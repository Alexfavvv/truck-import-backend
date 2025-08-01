<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PartType extends Model
{
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_part_type', 'part_type_id', 'brand_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function metatags(): MorphOne
    {
        return $this->morphOne(Metatag::class, 'metatagable');
    }
}
