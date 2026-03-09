<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    /** Профили компаний: список компаний (admin_customer) с табами Активные / Архив */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $tab = $request->get('tab', 'active');

        $companies = collect();
        if ($tab === 'active') {
            $query = User::where('role', UserRole::AdminCustomer->value)
                ->where('is_active', true)
                ->withCount(['managers', 'companyOrders']);

            $search = $request->get('search');
            if ($search !== null && $search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('company', 'ilike', '%' . $search . '%')
                        ->orWhere('full_name', 'ilike', '%' . $search . '%');
                });
            }

            $companies = $query->orderBy('company')->paginate(20)->withQueryString();
        }

        return view('admin.users.index', compact('companies', 'tab'));
    }

    /** Карточка пользователя: для админ/менеджер заказчика — новая страница с табами, для остальных — старая */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $roleValue = $user->role?->value;
        $isCustomerSide = in_array($roleValue, [UserRole::AdminCustomer->value, UserRole::ManagerCustomer->value], true);

        if ($isCustomerSide) {
            $user->load([
                'parent',
                'orders' => fn($q) => $q->with('invoice')->latest(),
                'companyOrders' => fn($q) => $q->with('invoice')->latest(),
            ]);
            return view('admin.managers.show', compact('user'));
        }

        $user->load(['managers', 'orders' => fn($q) => $q->latest()]);
        return view('admin.users.show', compact('user'));
    }

    /** Карточка компании (профиль компании, не админа) */
    public function showCompany(Request $request, User $user): View
    {
        if ($user->role !== UserRole::AdminCustomer) {
            abort(404);
        }
        $this->authorize('view', $user);
        $user->loadCount('companyOrders');
        $user->load(['managers' => fn($q) => $q->withCount('orders'), 'companyOrders' => fn($q) => $q->latest()]);

        $companyUsers = collect([
            (object)['user' => $user, 'role_label' => 'Администратор', 'orders_count' => $user->company_orders_count],
        ])->merge(
            $user->managers->map(fn($m) => (object)['user' => $m, 'role_label' => 'Менеджер', 'orders_count' => $m->orders_count])
        );

        $search = $request->get('search');
        if ($search !== null && $search !== '') {
            $search = mb_strtolower($search);
            $companyUsers = $companyUsers->filter(function ($row) use ($search) {
                $u = $row->user;
                $name = mb_strtolower($u->full_name ?? $u->name ?? '');
                $customerId = mb_strtolower((string)($u->customer_id ?? ''));
                return str_contains($name, $search) || str_contains($customerId, $search);
            });
        }

        return view('admin.companies.show', compact('user', 'companyUsers'));
    }

    /** Сохранение реквизитов компании */
    public function updateCompany(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::AdminCustomer) {
            abort(404);
        }
        $this->authorize('update', $user);

        $validated = $request->validate([
            'company'               => 'nullable|string|max:255',
            'inn'                   => 'nullable|string|max:50',
            'ogrn'                  => 'nullable|string|max:50',
            'bank_account'          => 'nullable|string|max:50',
            'bank'                  => 'nullable|string|max:255',
            'bik'                   => 'nullable|string|max:50',
            'correspondent_account' => 'nullable|string|max:50',
        ]);

        $user->update($validated);

        return redirect()->route('admin.companies.show', $user)
            ->with('success', 'Изменения сохранены');
    }

    /** Форма создания пользователя */
    public function create(Request $request): View
    {
        $this->authorize('create', User::class);
        $roles = $this->getAllowedRoles();
        $adminCustomers = User::where('role', UserRole::AdminCustomer->value)->get();
        $selectedRole = $request->get('role');
        $parentId = $request->get('parent_id');
        return view('admin.users.create', compact('roles', 'adminCustomers', 'selectedRole', 'parentId'));
    }

    /** Создание пользователя */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'role'      => 'required|in:' . implode(',', UserRole::values()),
            'full_name' => 'required|string|max:255',
            'email'     => 'nullable|email|unique:users,email',
            'phone'     => 'required|string|max:30',
            'company'   => 'nullable|string|max:255',
            'requisites' => 'nullable|string',
            'position'  => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:users,id',
            'login'     => 'nullable|string|max:100|unique:users,login',
            'password'  => 'nullable|string|min:8',
        ]);

        $role = UserRole::from($validated['role']);
        $authUser = Auth::user();

        // admin_customer может создавать только manager_customer
        if ($authUser->isAdminCustomer() && $role !== UserRole::ManagerCustomer) {
            abort(403);
        }

        // admin_truck может быть только один в системе
        if ($role === UserRole::AdminTruck) {
            abort(403, 'Администратор Трак Импорт может быть только один.');
        }

        $result = match($role) {
            UserRole::ManagerTruck    => ['user' => $this->userService->createManagerTruck($validated)],
            UserRole::AdminCustomer   => ['user' => $this->userService->createAdminCustomer($validated)],
            UserRole::ManagerCustomer => $this->createManagerCustomer($validated, $authUser),
            default                   => abort(403),
        };

        $message = 'Пользователь создан';
        if (isset($result['password'])) {
            $message .= ". Логин: {$result['user']->login}, пароль: {$result['password']}";
        }

        $user = $result['user'];
        $redirectRoute = $user->role === UserRole::AdminCustomer
            ? 'admin.companies.show'
            : 'admin.users.show';

        return redirect()->route($redirectRoute, $user)
            ->with('success', $message);
    }

    /** Форма редактирования (только admin_truck или admin_customer для своих) */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        return view('admin.users.edit', compact('user'));
    }

    /** Сохранение изменений */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'full_name'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|unique:users,email,' . $user->id,
            'phone'      => 'nullable|string|max:30',
            'company'    => 'nullable|string|max:255',
            'requisites' => 'nullable|string',
            'position'   => 'nullable|string|max:255',
            'login'      => 'nullable|string|max:100|unique:users,login,' . $user->id,
            'password'   => 'nullable|string|min:8',
            'is_active'  => 'boolean',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        $tab = $request->get('_return_tab', 'personal');
        $url = route('admin.users.show', $user);
        if (in_array($tab, ['personal', 'auth', 'orders', 'invoices'], true)) {
            $url .= '?tab=' . urlencode($tab);
        }

        return redirect()->to($url)->with('success', 'Данные обновлены');
    }

    /** Удаление пользователя */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        match($user->role) {
            UserRole::AdminCustomer   => $this->userService->deleteAdminCustomer($user),
            UserRole::ManagerCustomer => $this->userService->deleteManagerCustomer($user),
            UserRole::ManagerTruck    => $this->userService->deleteManagerTruck($user),
            default                   => $user->delete(),
        };

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь удалён');
    }

    private function createManagerCustomer(array $data, User $authUser): array
    {
        $parentId = $data['parent_id'] ?? null;
        $adminCustomer = $authUser->isAdminCustomer()
            ? $authUser
            : User::findOrFail($parentId);

        return $this->userService->createManagerCustomer($adminCustomer, $data);
    }

    private function getAllowedRoles(): array
    {
        $user = Auth::user();
        if ($user->isAdminTruck()) {
            // admin_truck создать нельзя — только один в системе
            return array_filter(
                UserRole::cases(),
                fn($r) => $r !== UserRole::AdminTruck
            );
        }
        if ($user->isAdminCustomer()) {
            return [UserRole::ManagerCustomer];
        }
        return [];
    }
}
