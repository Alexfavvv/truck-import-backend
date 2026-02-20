<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Видит ли пользователь этот заказ */
    public function view(User $user, Order $order): bool
    {
        if ($user->isTruckSide()) {
            return true;
        }

        if ($user->isAdminCustomer()) {
            // Видит заказы своей компании (свои + менеджеров)
            return $order->company_admin_id === $user->id
                || $order->user_id === $user->id;
        }

        if ($user->isManagerCustomer()) {
            return $order->user_id === $user->id;
        }

        return false;
    }

    /** Создание заказа — все 4 роли */
    public function create(User $user): bool
    {
        return $user->role !== null;
    }

    /** Смена статуса — только Трак Импорт */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->canChangeOrderStatus();
    }

    /** Удаление — только admin_truck */
    public function delete(User $user, Order $order): bool
    {
        return $user->canDeleteOrder();
    }
}
