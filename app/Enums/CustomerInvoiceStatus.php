<?php

namespace App\Enums;

enum CustomerInvoiceStatus: string
{
    case Pending   = 'pending';
    case Paid      = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Ожидает',
            self::Paid      => 'Оплачено',
            self::Cancelled => 'Отменено',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending   => 'badge-secondary',
            self::Paid      => 'badge-success',
            self::Cancelled => 'badge-danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
