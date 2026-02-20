<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminTruck    = 'admin_truck';
    case ManagerTruck  = 'manager_truck';
    case AdminCustomer = 'admin_customer';
    case ManagerCustomer = 'manager_customer';

    public function label(): string
    {
        return match($this) {
            self::AdminTruck     => 'Администратор Трак Импорт',
            self::ManagerTruck   => 'Менеджер Трак Импорт',
            self::AdminCustomer  => 'Администратор Заказчика',
            self::ManagerCustomer => 'Менеджер Заказчика',
        };
    }

    public function isTruckSide(): bool
    {
        return in_array($this, [self::AdminTruck, self::ManagerTruck]);
    }

    public function isCustomerSide(): bool
    {
        return in_array($this, [self::AdminCustomer, self::ManagerCustomer]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
