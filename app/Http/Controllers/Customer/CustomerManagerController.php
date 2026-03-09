<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerManagerController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): View
    {
        $this->ensureAdminCustomer();
        $query = Auth::user()->managers()->withCount('orders');
        if ($request->filled('search')) {
            $term = '%' . $request->get('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }
        $managers = $query->orderBy('full_name')->get();
        return view('customer.managers.index', compact('managers'));
    }

    public function create(): View
    {
        $this->ensureAdminCustomer();
        return view('customer.managers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminCustomer();

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'required|string|max:30',
            'email'     => 'nullable|email|unique:users,email',
        ], [
            'full_name.required' => 'ФИО обязательно',
            'phone.required'     => 'Телефон обязателен',
        ]);

        $result = $this->userService->createManagerCustomer(Auth::user(), $validated);

        return redirect()->route('customer.managers.show', $result['user'])
            ->with('success', sprintf(
                'Менеджер создан. Логин: %s, пароль: %s — сохраните данные!',
                $result['user']->login,
                $result['password']
            ));
    }

    public function show(Request $request, User $manager): View
    {
        $this->ensureAdminCustomer();
        $this->ensureOwns($manager);

        $tab = $request->get('tab', 'personal');
        $tab = in_array($tab, ['personal', 'auth', 'orders', 'invoices'], true) ? $tab : 'personal';

        $manager->load(['parent', 'orders' => fn ($q) => $q->latest()->with('invoice')]);
        $ordersList = $manager->orders;
        $invoices = $ordersList->map->invoice->filter()->values();
        $canEdit = true; // admin_customer может редактировать своего менеджера

        return view('customer.managers.show', compact('manager', 'tab', 'ordersList', 'invoices', 'canEdit'));
    }

    public function update(Request $request, User $manager): RedirectResponse
    {
        $this->ensureAdminCustomer();
        $this->ensureOwns($manager);

        $rules = [
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'email'     => 'nullable|email|unique:users,email,' . $manager->id,
            'login'     => 'nullable|string|max:100|unique:users,login,' . $manager->id,
            'password'  => 'nullable|string|min:8|confirmed',
        ];
        $validated = $request->validate($rules);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        $manager->update($validated);

        $tab = $request->get('_return_tab', 'personal');
        return redirect()->route('customer.managers.show', [$manager, 'tab' => $tab])->with('success', 'Данные обновлены');
    }

    public function destroy(User $manager): RedirectResponse
    {
        $this->ensureAdminCustomer();
        $this->ensureOwns($manager);
        $this->userService->deleteManagerCustomer($manager);
        return redirect()->route('customer.managers.index')
            ->with('success', 'Менеджер удалён. Его заказы сохранены.');
    }

    private function ensureAdminCustomer(): void
    {
        if (!Auth::user()->isAdminCustomer()) {
            abort(403, 'Доступ только для администратора заказчика');
        }
    }

    private function ensureOwns(User $manager): void
    {
        if ($manager->parent_id !== Auth::id()) {
            abort(403);
        }
    }
}
