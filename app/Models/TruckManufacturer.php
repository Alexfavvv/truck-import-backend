<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class TruckManufacturer extends Model
{
    use HasFactory;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_truck_manufacturer', 'truck_manufacturer_id', 'product_id');
    }

    public function metatags(): MorphOne
    {
        return $this->morphOne(Metatag::class, 'metatagable');
    }
}
