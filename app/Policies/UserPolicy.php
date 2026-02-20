<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Просмотр всех пользователей — только admin_truck */
    public function viewAny(User $authUser): bool
    {
        return $authUser->isAdminTruck();
    }

    /** Просмотр карточки пользователя */
    public function view(User $authUser, User $target): bool
    {
        if ($authUser->isAdminTruck()) {
            return true;
        }

        // admin_customer видит своих менеджеров
        if ($authUser->isAdminCustomer()) {
            return $target->parent_id === $authUser->id
                || $target->id === $authUser->id;
        }

        return $authUser->id === $target->id;
    }

    /** Создание пользователя */
    public function create(User $authUser): bool
    {
        // admin_truck создаёт кого угодно
        // admin_customer создаёт только менеджеров своей компании
        return $authUser->isAdminTruck() || $authUser->isAdminCustomer();
    }

    /** Редактирование данных пользователя */
    public function update(User $authUser, User $target): bool
    {
        if ($authUser->isAdminTruck()) {
            return true;
        }

        // admin_customer редактирует только себя и своих менеджеров
        if ($authUser->isAdminCustomer()) {
            return $target->id === $authUser->id
                || $target->parent_id === $authUser->id;
        }

        // Менеджеры не редактируют свои данные — только admin_truck
        return false;
    }

    /** Удаление пользователя */
    public function delete(User $authUser, User $target): bool
    {
        if ($authUser->isAdminTruck()) {
            return true;
        }

        // admin_customer удаляет своих менеджеров или себя
        if ($authUser->isAdminCustomer()) {
            return $target->parent_id === $authUser->id
                || $target->id === $authUser->id;
        }

        return false;
    }
}
