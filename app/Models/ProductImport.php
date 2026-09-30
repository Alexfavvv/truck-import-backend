<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['metadata' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
}
