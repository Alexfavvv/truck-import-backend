<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        if (!$this->relationLoaded('product')) {
            $this->load('product');
        }
        
        return $this->product->price * $this->quantity;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($cartItem) {
            if ($cartItem->quantity < 1) {
                throw new \InvalidArgumentException('Quantity must be at least 1');
            }
        });
    }
}
