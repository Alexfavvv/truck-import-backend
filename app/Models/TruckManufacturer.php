<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class TruckManufacturer extends Model
{
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'truck_manufacturer_product', 'truck_manufacturer_id', 'product_id');
    }

    public function metatags(): MorphOne
    {
        return $this->morphOne(Metatag::class, 'metatagable');
    }
}
