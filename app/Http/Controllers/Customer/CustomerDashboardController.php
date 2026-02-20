<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(): View
    {
        $user = Auth::user();

        $recentOrders = $this->orderService
            ->getOrdersForUser($user, 5);

        $recentInvoices = $this->invoiceService
            ->getInvoicesForUser($user, 5);

        $totalOrders   = $user->orders()->count();
        $pendingInvoices = $user->isAdminCustomer()
            ? \App\Models\Invoice::whereHas('order', fn($q) =>
                $q->where('company_admin_id', $user->id)->orWhere('user_id', $user->id)
              )->where('truck_status', 'pending_payment')->count()
            : \App\Models\Invoice::whereHas('order', fn($q) =>
                $q->where('user_id', $user->id)
              )->where('truck_status', 'pending_payment')->count();

        return view('customer.dashboard', compact(
            'recentOrders', 'recentInvoices', 'totalOrders', 'pendingInvoices'
        ));
    }
}
