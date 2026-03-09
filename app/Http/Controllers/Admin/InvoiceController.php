<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\TruckInvoiceStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $tab = $request->get('tab', 'my');
        if (!in_array($tab, ['my', 'company', 'create'], true)) {
            $tab = 'my';
        }

        $filters = [
            'status'      => $request->get('status'),
            'number'      => $request->get('number'),
            'company_id'  => $request->get('company_id'),
            'manager_id'  => $request->get('manager_id'),
        ];

        $companies = collect();
        $managers = collect();
        $statusOptions = [];

        if ($tab === 'create') {
            $orders = $this->invoiceService->getOrdersWithoutInvoice($user, $filters);
            $invoices = null;

            if ($user->isTruckSide()) {
                $companies = User::where('role', UserRole::AdminCustomer->value)->orderBy('company')->get();
                $adminTruck = User::where('role', UserRole::AdminTruck->value)->first();
                if ($adminTruck) {
                    $companies->prepend($adminTruck);
                }
                $managers = User::where('role', UserRole::ManagerCustomer->value)->orderBy('full_name')->get();
            }
        } else {
            $invoices = $this->invoiceService->getInvoicesForUserWithFilters($user, $tab, $filters);
            $orders = null;

            $statusOptions = [
                ['value' => TruckInvoiceStatus::PendingPayment->value, 'label' => TruckInvoiceStatus::PendingPayment->label()],
                ['value' => TruckInvoiceStatus::Paid->value, 'label' => TruckInvoiceStatus::Paid->label()],
                ['value' => TruckInvoiceStatus::Cancelled->value, 'label' => TruckInvoiceStatus::Cancelled->label()],
            ];

            if ($user->isTruckSide()) {
                $companies = User::where('role', UserRole::AdminCustomer->value)->orderBy('company')->get();
                $adminTruck = User::where('role', UserRole::AdminTruck->value)->first();
                if ($adminTruck) {
                    $companies->prepend($adminTruck);
                }
                $managers = User::where('role', UserRole::ManagerCustomer->value)->orderBy('full_name')->get();
            }
        }

        return view('admin.invoices.index', compact('invoices', 'orders', 'tab', 'companies', 'managers', 'statusOptions'));
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

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Счёт #' . $invoice->invoice_number . ' создан');
    }

    public function show(Invoice $invoice): RedirectResponse
    {
        $this->authorize('view', $invoice);
        return redirect()->route('admin.invoices.pdf', $invoice);
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
