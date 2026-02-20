<?php

namespace App\Services;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\TruckInvoiceStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    /**
     * Возвращает список счетов с учётом прав пользователя.
     */
    public function getInvoicesForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        $query = Invoice::with(['order.user', 'creator'])->latest();

        if ($user->isAdminTruck()) {
            // Все счета
        } elseif ($user->isManagerTruck()) {
            // Только созданные им (он не создаёт, но видит свои — созданные admin_truck для его заказов)
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->isAdminCustomer()) {
            // Счета компании: счета заказов своей компании
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('company_admin_id', $user->id)
                  ->orWhere('user_id', $user->id);
            });
        } elseif ($user->isManagerCustomer()) {
            // Счета для своих заказов
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('company_admin_id', $user->parent_id);
            });
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
