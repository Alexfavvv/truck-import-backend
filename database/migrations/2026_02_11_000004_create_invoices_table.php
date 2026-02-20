<?php

use App\Enums\CustomerInvoiceStatus;
use App\Enums\TruckInvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            // Статус со стороны Трак Импорт (меняют admin_truck и manager_truck)
            $table->string('truck_status')->default(TruckInvoiceStatus::PendingPayment->value);
            // Статус со стороны Заказчика (меняют admin_customer и manager_customer)
            $table->string('customer_status')->default(CustomerInvoiceStatus::Pending->value);
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
