<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $tab = $request->get('tab', 'my');
        if (!in_array($tab, ['my', 'company', 'archive'], true)) {
            $tab = 'my';
        }

        $filters = [
            'status'      => $request->get('status'),
            'number'      => $request->get('number'),
            'company_id'  => $request->get('company_id'),
            'manager_id'  => $request->get('manager_id'),
        ];

        $orders   = $this->orderService->getOrdersForUser($user, $tab, $filters);
        $statuses  = OrderStatus::filterOptions();

        $companies = collect();
        $managers  = collect();

        if ($user->isTruckSide()) {
            $companies = User::where('role', UserRole::AdminCustomer->value)
                ->orderBy('company')
                ->get();

            // Добавляем главного админа Трак Импорт как компанию "Трак Импорт"
            $adminTruck = User::where('role', UserRole::AdminTruck->value)->first();
            if ($adminTruck) {
                $companies->prepend($adminTruck);
            }

            $managers = User::where('role', UserRole::ManagerCustomer->value)
                ->orderBy('full_name')
                ->get();
        }

        return view('admin.orders.index', compact('orders', 'statuses', 'tab', 'companies', 'managers'));
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
        $statusOptions = OrderStatus::filterOptions();
        return view('admin.orders.show', compact('order', 'statusOptions'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', OrderStatus::changeStatusValues()),
        ]);

        $status = $validated['status'] === 'in_progress'
            ? OrderStatus::Created
            : OrderStatus::from($validated['status']);

        $this->orderService->updateStatus($order, $status);

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
