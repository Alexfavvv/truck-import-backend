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
            self::Completed     => 'Выполнен',
            self::Cancelled     => 'Отменён',
            self::Created,
            self::Paid,
            self::Unpaid,
            self::ArrivedEurope,
            self::ShippedRussia,
            self::ArrivedRussia => 'В работе',
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

    /**
     * Опции для фильтра заказов и смены статуса: три статуса (В работе, Выполнен, Отменён).
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function filterOptions(): array
    {
        return [
            ['value' => 'in_progress', 'label' => 'В работе'],
            ['value' => self::Completed->value, 'label' => 'Выполнен'],
            ['value' => self::Cancelled->value, 'label' => 'Отменён'],
        ];
    }

    /**
     * Допустимые значения при смене статуса в карточке заказа (3 статуса).
     */
    public static function changeStatusValues(): array
    {
        return ['in_progress', self::Completed->value, self::Cancelled->value];
    }

    /**
     * Значения статусов, относящихся к «В работе».
     *
     * @return array<string>
     */
    public static function inProgressValues(): array
    {
        return [
            self::Created->value,
            self::Paid->value,
            self::Unpaid->value,
            self::ArrivedEurope->value,
            self::ShippedRussia->value,
            self::ArrivedRussia->value,
        ];
    }
}
