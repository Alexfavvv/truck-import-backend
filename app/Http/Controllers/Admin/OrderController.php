<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(): View
    {
        $orders = $this->orderService->getOrdersForUser(Auth::user());
        $statuses = OrderStatus::cases();
        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function create(): View
    {
        $statuses = OrderStatus::cases();
        return view('admin.orders.create', compact('statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $validated = $request->validate([
            'notes'               => 'nullable|string|max:2000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.price'       => 'required|numeric|min:0',
        ]);

        $order = $this->orderService->createOrder(Auth::user(), $validated);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Заказ #' . $order->order_number . ' создан');
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['items.product', 'user', 'invoice']);
        $statuses = OrderStatus::cases();
        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', OrderStatus::values()),
        ]);

        $this->orderService->updateStatus($order, OrderStatus::from($validated['status']));

        return back()->with('success', 'Статус заказа обновлён');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);
        $this->orderService->deleteOrder($order);
        return redirect()->route('admin.orders.index')
            ->with('success', 'Заказ удалён');
    }
}
