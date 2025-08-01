<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Brand extends Model
{
    use HasFactory;
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function truckModels(): HasMany
    {
        return $this->hasMany(TruckModel::class);
    }

    public function partTypes(): BelongsToMany
    {
        return $this->belongsToMany(PartType::class, 'brand_part_type', 'brand_id', 'part_type_id');
    }

    public function metatags(): MorphOne
    {
        return $this->morphOne(Metatag::class, 'metatagable');
    }
}
