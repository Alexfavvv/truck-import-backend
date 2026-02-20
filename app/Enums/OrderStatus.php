<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Created        = 'created';
    case Paid           = 'paid';
    case Unpaid         = 'unpaid';
    case ArrivedEurope  = 'arrived_europe';
    case ShippedRussia  = 'shipped_russia';
    case ArrivedRussia  = 'arrived_russia';
    case Completed      = 'completed';
    case Cancelled      = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Created       => 'Создано',
            self::Paid          => 'Оплачено',
            self::Unpaid        => 'Не оплачено',
            self::ArrivedEurope => 'Поступил на склад в Европе',
            self::ShippedRussia => 'Отправлен на склад в Россию',
            self::ArrivedRussia => 'Доставлен на склад в Россию',
            self::Completed     => 'Получен (Выполнен)',
            self::Cancelled     => 'Отменён',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Created       => 'badge-secondary',
            self::Paid          => 'badge-success',
            self::Unpaid        => 'badge-warning',
            self::ArrivedEurope => 'badge-info',
            self::ShippedRussia => 'badge-primary',
            self::ArrivedRussia => 'badge-primary',
            self::Completed     => 'badge-success',
            self::Cancelled     => 'badge-danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
