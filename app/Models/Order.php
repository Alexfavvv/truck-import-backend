<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'company_admin_id', 'status', 'notes',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    // ─── Связи ───────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** admin_customer компании, к которой относится заказ */
    public function companyAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_admin_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ─── Атрибуты ────────────────────────────────────────────────

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->price * $item->quantity);
    }

    // ─── Генерация номера ─────────────────────────────────────────

    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::withTrashed()
            ->whereYear('created_at', $year)
            ->count();
        return sprintf('ORD-%d-%05d', $year, $last + 1);
    }
}
