<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engine extends Model
{
    protected $fillable = [
        'slug', 'title', 'manufacturer', 'model', 'type', 'sku', 'price',
        'currency', 'availability', 'image', 'description', 'engine_family',
        'displacement', 'power', 'fuel_type', 'application', 'condition',
    ];

    protected $casts = ['price' => 'decimal:2'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
