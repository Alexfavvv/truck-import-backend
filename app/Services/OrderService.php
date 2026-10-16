<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Возвращает список заказов с учётом прав пользователя, вкладки и фильтров.
     *
     * @param  User  $user
     * @param  string  $scope   my|company|archive
     * @param  array  $filters  ['status' => ?, 'number' => ?, 'company_id' => ?, 'manager_id' => ?]
     */
    public function getOrdersForUser(User $user, string $scope = 'my', array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $scope = in_array($scope, ['my', 'company', 'archive'], true) ? $scope : 'my';

        $query = Order::with(['user', 'companyAdmin'])->latest();

        // ─── Базовая выборка по роли и вкладке ────────────────────────────────

        if ($user->isManagerTruck()) {
            $query->whereIn('company_admin_id', $user->assignedCompanies()->select('users.id'));
            if ($scope === 'archive') {
                $query->whereIn('status', [OrderStatus::Completed, OrderStatus::Cancelled]);
            }
        } elseif ($user->isTruckSide()) {
            // Для Трак Импорт:
            // - my: только свои заказы
            // - company: все заказы
            // - archive: все выполненные/отменённые
            if ($scope === 'my') {
                $query->where('user_id', $user->id);
            } elseif ($scope === 'archive') {
                $query->whereIn('status', [OrderStatus::Completed, OrderStatus::Cancelled]);
            }
        } elseif ($user->isAdminCustomer()) {
            // Для admin_customer:
            // - my: только свои
            // - company/archive: заказы своей компании (свои + менеджеров)
            if ($scope === 'my') {
                $query->where('user_id', $user->id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('company_admin_id', $user->id)
                      ->orWhere('user_id', $user->id);
                });

                if ($scope === 'archive') {
                    $query->whereIn('status', [OrderStatus::Completed, OrderStatus::Cancelled]);
                }
            }
        } elseif ($user->isManagerCustomer()) {
            // Для manager_customer: только свои, archive — только выполненные/отменённые
            $query->where('user_id', $user->id);
            if ($scope === 'archive') {
                $query->whereIn('status', [OrderStatus::Completed, OrderStatus::Cancelled]);
            }
        }

        // ─── Фильтры ──────────────────────────────────────────────────────────

        // Статус — только для вкладок my/company (три варианта: Выполнен, Отменен, В работе)
        if (($scope === 'my' || $scope === 'company') && !empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'in_progress') {
                $query->whereIn('status', OrderStatus::inProgressValues());
            } elseif (in_array($status, OrderStatus::values(), true)) {
                $query->where('status', $status);
            }
        }

        // Поиск по номеру заказа
        if (!empty($filters['number'])) {
            $number = trim((string) $filters['number']);
            $query->where('order_number', 'like', '%' . $number . '%');
        }

        // Фильтр по компании (для Трак Импорт на вкладках company/archive)
        if ($user->isTruckSide() && ($scope === 'company' || $scope === 'archive') && !empty($filters['company_id'])) {
            $companyId = (int) $filters['company_id'];

            // Если выбрана компания Трак Импорт (главный админ), показываем заказы без company_admin_id
            $adminTruckId = User::where('role', UserRole::AdminTruck->value)->value('id');

            if ($user->isAdminTruck() && $adminTruckId && $companyId === (int) $adminTruckId) {
                $query->whereNull('company_admin_id');
            } else {
                $query->where('company_admin_id', $companyId);
            }
        }

        // Фильтр по менеджеру (для Трак Импорт на вкладках company/archive)
        if ($user->isTruckSide() && ($scope === 'company' || $scope === 'archive') && !empty($filters['manager_id'])) {
            $query->where('user_id', (int) $filters['manager_id']);
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
                UserRole::ManagerTruck => (int) ($data['company_admin_id'] ?? 0),
                default                   => null,
            };

            if ($creator->isManagerTruck() && !$creator->managesCompany($companyAdminId)) {
                throw ValidationException::withMessages(['company_admin_id' => 'Выберите закреплённую за вами компанию.']);
            }

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
     * Заказ по выбранным строкам поиска (product_id + quantity).
     *
     * @param  list<array{product_id: int, quantity: int}>  $rows
     *
     * @throws ValidationException
     */
    public function createOrderFromSearchSelection(User $creator, array $rows, string $notesPrefix = 'Заявка со страницы поиска', ?int $companyAdminId = null): Order
    {
        if ($rows === []) {
            throw ValidationException::withMessages([
                'items' => ['Выберите хотя бы одну позицию.'],
            ]);
        }

        $byProductId = [];
        foreach ($rows as $row) {
            $pid = (int) ($row['product_id'] ?? 0);
            $qty = (int) ($row['quantity'] ?? 0);
            if ($pid < 1 || $qty < 1) {
                throw ValidationException::withMessages([
                    'items' => ['Некорректные данные позиции.'],
                ]);
            }
            $byProductId[$pid] = ($byProductId[$pid] ?? 0) + $qty;
        }

        $productIds = array_keys($byProductId);
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($productIds as $pid) {
            if ($products->get($pid) === null) {
                throw ValidationException::withMessages([
                    'items' => ["Товар #{$pid} не найден в каталоге."],
                ]);
            }
        }

        foreach ($byProductId as $pid => $qty) {
            $product = $products->get($pid);
            if ($product->quantity !== null && $qty > $product->quantity) {
                throw ValidationException::withMessages([
                    'items' => ["Недостаточно остатка для «{$product->sku}»: доступно {$product->quantity}, запрошено {$qty}."],
                ]);
            }
        }

        $items = [];
        foreach ($byProductId as $pid => $qty) {
            $product = $products->get($pid);
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => (float) $product->price,
            ];
        }

        $notes = $notesPrefix . ', ' . now()->format('d.m.Y H:i');

        return $this->createOrder($creator, [
            'notes' => $notes,
            'items' => $items,
            'company_admin_id' => $companyAdminId,
        ]);
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
