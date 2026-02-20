<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\TruckInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(): View
    {
        $invoices = $this->invoiceService->getInvoicesForUser(Auth::user());
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invoice::class);
        $orders = Order::whereDoesntHave('invoice')->latest()->get();
        $order = $request->order_id ? Order::findOrFail($request->order_id) : null;
        return view('admin.invoices.create', compact('orders', 'order'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount'   => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes'    => 'nullable|string|max:2000',
        ]);

        $order   = Order::findOrFail($validated['order_id']);
        $invoice = $this->invoiceService->createFromOrder($order, Auth::user(), $validated);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Счёт #' . $invoice->invoice_number . ' создан');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load(['order.items.product', 'order.user', 'creator']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function updateTruckStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('changeTruckStatus', $invoice);

        $validated = $request->validate([
            'truck_status' => 'required|in:' . implode(',', TruckInvoiceStatus::values()),
        ]);

        $this->invoiceService->updateTruckStatus($invoice, TruckInvoiceStatus::from($validated['truck_status']));

        return back()->with('success', 'Статус счёта обновлён');
    }

    public function updateCustomerStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('changeCustomerStatus', $invoice);

        $validated = $request->validate([
            'customer_status' => 'required|in:' . implode(',', CustomerInvoiceStatus::values()),
        ]);

        $this->invoiceService->updateCustomerStatus($invoice, CustomerInvoiceStatus::from($validated['customer_status']));

        return back()->with('success', 'Статус оплаты обновлён');
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $pdf = $this->invoiceService->generatePdf($invoice);
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);
        $this->invoiceService->deleteInvoice($invoice);
        return redirect()->route('admin.invoices.index')
            ->with('success', 'Счёт удалён');
    }
}
