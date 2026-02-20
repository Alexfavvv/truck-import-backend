<?php

namespace App\Http\Controllers\Customer;

use App\Enums\CustomerInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerInvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(): View
    {
        $invoices = $this->invoiceService->getInvoicesForUser(Auth::user());
        return view('customer.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load(['order.items.product', 'creator']);
        return view('customer.invoices.show', compact('invoice'));
    }

    public function updateStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('changeCustomerStatus', $invoice);

        $validated = $request->validate([
            'customer_status' => 'required|in:' . implode(',', CustomerInvoiceStatus::values()),
        ]);

        $this->invoiceService->updateCustomerStatus(
            $invoice,
            CustomerInvoiceStatus::from($validated['customer_status'])
        );

        return back()->with('success', 'Статус оплаты обновлён');
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $pdf = $this->invoiceService->generatePdf($invoice);
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
