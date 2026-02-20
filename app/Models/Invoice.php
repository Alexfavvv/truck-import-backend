<?php

namespace App\Models;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\TruckInvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'order_id', 'created_by',
        'amount', 'truck_status', 'customer_status',
        'due_date', 'paid_at', 'notes',
    ];

    protected $casts = [
        'truck_status'    => TruckInvoiceStatus::class,
        'customer_status' => CustomerInvoiceStatus::class,
        'due_date'        => 'date',
        'paid_at'         => 'datetime',
        'amount'          => 'float',
    ];

    // ─── Связи ───────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Генерация номера ─────────────────────────────────────────

    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->count();
        return sprintf('INV-%d-%05d', $year, $last + 1);
    }
}
