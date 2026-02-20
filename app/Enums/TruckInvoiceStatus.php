<?php

namespace App\Enums;

enum TruckInvoiceStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid           = 'paid';
    case Cancelled      = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PendingPayment => 'Ожидает оплату',
            self::Paid           => 'Оплачено',
            self::Cancelled      => 'Отменено',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PendingPayment => 'badge-warning',
            self::Paid           => 'badge-success',
            self::Cancelled      => 'badge-danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
