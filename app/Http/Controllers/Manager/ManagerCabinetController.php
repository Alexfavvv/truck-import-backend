<?php

namespace App\Http\Controllers\Manager;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManagerCabinetController extends Controller
{
    public function __construct(private readonly OrderService $orders, private readonly InvoiceService $invoices) {}

    public function dashboard(): RedirectResponse
    {
        return redirect()->route('manager.search');
    }

    public function search(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:255']]);
        $query = trim($data['q'] ?? '');
        $products = $query === '' ? null : Product::query()
            ->where(fn ($q) => $q->where('title', 'like', '%'.$query.'%')->orWhere('sku', 'like', '%'.$query.'%'))
            ->with('brand')->orderBy('title')->paginate(20)->withQueryString();
        return view('manager.search', compact('query', 'products'));
    }

    public function companies(Request $request): View
    {
        $companies = $request->user()->assignedCompanies()->orderBy('company')->paginate(20);
        return view('manager.companies', compact('companies'));
    }

    public function company(Request $request, User $company): View
    {
        abort_unless($request->user()->managesCompany($company->id), 403);
        return view('manager.company', compact('company'));
    }

    public function orders(Request $request): View
    {
        $data = $request->validate([
            'status' => ['nullable', 'string', 'max:40'],
            'number' => ['nullable', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer'],
        ]);
        $companies = $request->user()->assignedCompanies()->orderBy('company')->get();
        $orders = $this->orders->getOrdersForUser($request->user(), 'company', [
            'status' => $data['status'] ?? null,
            'number' => $data['number'] ?? null,
            'company_id' => $data['company_id'] ?? null,
        ]);
        return view('manager.orders.index', compact('orders', 'companies'));
    }

    public function createOrder(Request $request): View
    {
        $companies = $request->user()->assignedCompanies()->orderBy('company')->get();
        $data = $request->validate(['q' => ['nullable', 'string', 'max:255']]);
        $query = trim($data['q'] ?? '');
        $products = $query === '' ? collect() : Product::query()
            ->where(fn ($q) => $q->where('title', 'like', '%'.$query.'%')->orWhere('sku', 'like', '%'.$query.'%'))
            ->orderBy('title')->limit(50)->get();
        return view('manager.orders.create', compact('companies', 'products', 'query'));
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);
        $data = $request->validate([
            'company_admin_id' => ['required', 'integer', Rule::exists('truck_manager_company', 'company_admin_id')->where('manager_id', $request->user()->id)],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:0', 'max:999'],
        ]);
        $items = [];
        foreach ($data['quantities'] as $productId => $quantity) {
            if ((int) $quantity > 0) {
                $items[] = ['product_id' => (int) $productId, 'quantity' => (int) $quantity];
            }
        }
        if ($items === []) {
            throw ValidationException::withMessages(['quantities' => 'Укажите количество хотя бы для одного товара.']);
        }
        $order = $this->orders->createOrderFromSearchSelection(
            $request->user(), $items, 'Заказ из кабинета менеджера', (int) $data['company_admin_id']
        );
        return redirect()->route('manager.orders.show', $order)->with('success', 'Заказ создан.');
    }

    public function order(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['companyAdmin', 'user', 'items.product', 'invoice']);
        return view('manager.orders.show', compact('order'));
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);
        $data = $request->validate(['status' => ['required', Rule::in(OrderStatus::changeStatusValues())]]);
        $status = $data['status'] === 'in_progress' ? OrderStatus::Created : OrderStatus::from($data['status']);
        $this->orders->updateStatus($order, $status);
        return back()->with('success', 'Статус обновлён.');
    }

    public function invoices(Request $request): View
    {
        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer'],
        ]);
        $companies = $request->user()->assignedCompanies()->orderBy('company')->get();
        $invoices = $this->invoices->getInvoicesForUserWithFilters($request->user(), 'company', $data);
        return view('manager.invoices.index', compact('invoices', 'companies'));
    }

    public function invoice(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load(['order.companyAdmin', 'order.user', 'creator']);
        return view('manager.invoices.show', compact('invoice'));
    }

    public function invoicePdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return $this->invoices->generatePdf($invoice)->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function profile(Request $request): View
    {
        return view('manager.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);
        $user->update($data + ['name' => $data['full_name']]);
        return back()->with('success', 'Профиль сохранён.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (!Hash::check($data['current_password'], $request->user()->password)) {
            return back()->withErrors(['current_password' => 'Текущий пароль неверен.']);
        }
        $request->user()->update(['password' => $data['password']]);
        $request->user()->tokens()->delete();
        return back()->with('success', 'Пароль изменён.');
    }
}
