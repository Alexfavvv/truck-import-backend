<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Возвращает список заказов с учётом прав пользователя.
     */
    public function getOrdersForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        $query = Order::with(['user', 'companyAdmin'])->latest();

        if ($user->isTruckSide()) {
            // Видят все заказы
        } elseif ($user->isAdminCustomer()) {
            // Заказы своей компании (свои + менеджеров)
            $query->where(function ($q) use ($user) {
                $q->where('company_admin_id', $user->id)
                  ->orWhere('user_id', $user->id);
            });
        } elseif ($user->isManagerCustomer()) {
            // Только свои заказы
            $query->where('user_id', $user->id);
        }

        return $query->paginate($perPage);
    }

    /**
     * Создать заказ из переданных данных.
     */
    public function createOrder(User $creator, array $data): Order
    {
        return DB::transaction(function () use ($creator, $data) {
            // Определяем company_admin_id
            $companyAdminId = match($creator->role) {
                UserRole::AdminCustomer   => $creator->id,
                UserRole::ManagerCustomer => $creator->parent_id,
                default                   => null,
            };

            $order = Order::create([
                'order_number'    => Order::generateNumber(),
                'user_id'         => $creator->id,
                'company_admin_id' => $companyAdminId,
                'status'          => OrderStatus::Created,
                'notes'           => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] ?? [] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
            }

            return $order->load('items.product');
        });
    }

    /**
     * Изменить статус заказа (только Трак Импорт).
     */
    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $order->update(['status' => $status]);
        return $order;
    }

    /**
     * Удалить заказ (только admin_truck).
     */
    public function deleteOrder(Order $order): void
    {
        $order->delete();
    }
}
