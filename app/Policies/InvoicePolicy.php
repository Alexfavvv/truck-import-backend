<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /** Видит ли пользователь счёт */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isTruckSide()) {
            return true;
        }

        // Заказчики видят счета своей компании через заказ
        if ($user->isCustomerSide() && $invoice->order) {
            $order = $invoice->order;
            if ($user->isAdminCustomer()) {
                return $order->company_admin_id === $user->id
                    || $order->user_id === $user->id;
            }
            if ($user->isManagerCustomer()) {
                return $order->user_id === $user->id
                    || ($order->companyAdmin && $order->company_admin_id === $user->parent_id);
            }
        }

        return false;
    }

    /** Создание — только admin_truck */
    public function create(User $user): bool
    {
        return $user->canCreateInvoice();
    }

    /** Удаление — только admin_truck */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->canDeleteInvoice();
    }

    /** Смена статуса со стороны Трак Импорт */
    public function changeTruckStatus(User $user, Invoice $invoice): bool
    {
        return $user->canChangeTruckInvoiceStatus();
    }

    /** Смена статуса со стороны Заказчика */
    public function changeCustomerStatus(User $user, Invoice $invoice): bool
    {
        return $user->canChangeCustomerInvoiceStatus() && $this->view($user, $invoice);
    }
}
