<?php

namespace App\Services;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\TruckInvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    /**
     * Возвращает список счетов с учётом прав пользователя (без табов/фильтров).
     */
    public function getInvoicesForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->getInvoicesForUserWithFilters($user, 'my', [], $perPage);
    }

    /**
     * Список счетов по вкладке и фильтрам.
     *
     * @param  array{status?: string, number?: string, company_id?: string|int, manager_id?: string|int}  $filters
     */
    public function getInvoicesForUserWithFilters(User $user, string $tab, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $tab = $tab === 'company' ? 'company' : 'my';

        $query = Invoice::with(['order.user', 'order.companyAdmin', 'creator'])->latest();

        if ($user->isAdminTruck()) {
            if ($tab === 'my') {
                $query->where('created_by', $user->id);
            }
        } elseif ($user->isManagerTruck()) {
            $query->whereHas('order', fn ($q) => $q->where('user_id', $user->id));
            if ($tab === 'company') {
                // Для менеджера "company" не меняет выборку — те же счета
            }
        } elseif ($user->isAdminCustomer()) {
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('company_admin_id', $user->id)->orWhere('user_id', $user->id);
            });
        } elseif ($user->isManagerCustomer()) {
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('company_admin_id', $user->parent_id);
            });
        }

        // Фильтр по статусу (truck_status)
        if (!empty($filters['status']) && in_array($filters['status'], TruckInvoiceStatus::values(), true)) {
            $query->where('truck_status', $filters['status']);
        }

        // Поиск по номеру счёта
        if (!empty($filters['number'])) {
            $number = trim((string) $filters['number']);
            $query->where('invoice_number', 'like', '%'.$number.'%');
        }

        // Фильтр по компании (для ТИ на вкладках my и company)
        if ($user->isTruckSide() && !empty($filters['company_id'])) {
            $companyId = (int) $filters['company_id'];
            $adminTruckId = User::where('role', UserRole::AdminTruck->value)->value('id');
            if ($adminTruckId && $companyId === (int) $adminTruckId) {
                $query->whereHas('order', fn ($q) => $q->whereNull('company_admin_id'));
            } else {
                $query->whereHas('order', fn ($q) => $q->where('company_admin_id', $companyId));
            }
        }

        // Фильтр по менеджеру (для ТИ на вкладках my и company)
        if ($user->isTruckSide() && !empty($filters['manager_id'])) {
            $query->whereHas('order', fn ($q) => $q->where('user_id', (int) $filters['manager_id']));
        }

        return $query->paginate($perPage);
    }

    /**
     * Заказы без счёта (для вкладки «Выставить счёт»). Только в работе.
     *
     * @param  array{number?: string, company_id?: string|int, manager_id?: string|int}  $filters
     */
    public function getOrdersWithoutInvoice(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Order::with(['user', 'companyAdmin'])
            ->whereDoesntHave('invoice')
            ->whereIn('status', OrderStatus::inProgressValues())
            ->latest();

        if ($user->isManagerTruck()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isAdminCustomer()) {
            $query->where(function ($q) use ($user) {
                $q->where('company_admin_id', $user->id)->orWhere('user_id', $user->id);
            });
        } elseif ($user->isManagerCustomer()) {
            $query->where('user_id', $user->id);
        }
        // admin_truck видит все заказы без счёта

        if (!empty($filters['number'])) {
            $number = trim((string) $filters['number']);
            $query->where('order_number', 'like', '%'.$number.'%');
        }

        if ($user->isTruckSide() && !empty($filters['company_id'])) {
            $companyId = (int) $filters['company_id'];
            $adminTruckId = User::where('role', UserRole::AdminTruck->value)->value('id');
            if ($adminTruckId && $companyId === (int) $adminTruckId) {
                $query->whereNull('company_admin_id');
            } else {
                $query->where('company_admin_id', $companyId);
            }
        }

        if ($user->isTruckSide() && !empty($filters['manager_id'])) {
            $query->where('user_id', (int) $filters['manager_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Создать счёт для заказа (только admin_truck).
     */
    public function createFromOrder(Order $order, User $creator, array $data): Invoice
    {
        return Invoice::create([
            'invoice_number'  => Invoice::generateNumber(),
            'order_id'        => $order->id,
            'created_by'      => $creator->id,
            'amount'          => $data['amount'] ?? $order->total,
            'truck_status'    => TruckInvoiceStatus::PendingPayment,
            'customer_status' => CustomerInvoiceStatus::Pending,
            'due_date'        => $data['due_date'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ]);
    }

    /**
     * Обновить статус счёта со стороны Трак Импорт.
     */
    public function updateTruckStatus(Invoice $invoice, TruckInvoiceStatus $status): Invoice
    {
        $data = ['truck_status' => $status];
        if ($status === TruckInvoiceStatus::Paid) {
            $data['paid_at'] = now();
        }
        $invoice->update($data);
        return $invoice;
    }

    /**
     * Обновить статус счёта со стороны Заказчика.
     */
    public function updateCustomerStatus(Invoice $invoice, CustomerInvoiceStatus $status): Invoice
    {
        $invoice->update(['customer_status' => $status]);
        return $invoice;
    }

    /**
     * Сгенерировать PDF счёта.
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['order.items.product', 'order.user', 'creator']);

        return Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');
    }

    /**
     * Удалить счёт (только admin_truck).
     */
    public function deleteInvoice(Invoice $invoice): void
    {
        $invoice->delete();
    }
}
