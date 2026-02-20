<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(): View
    {
        $orders = $this->orderService->getOrdersForUser(Auth::user());
        return view('customer.orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('customer.orders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notes'               => 'nullable|string|max:2000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.price'       => 'required|numeric|min:0',
        ]);

        $order = $this->orderService->createOrder(Auth::user(), $validated);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Заказ #' . $order->order_number . ' успешно создан!');
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['items.product', 'invoice']);
        return view('customer.orders.show', compact('order'));
    }
}
