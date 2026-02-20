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

    /** Список всех пользователей (только admin_truck) */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('parent')->orderBy('role')->paginate(20);
        $roles = UserRole::cases();
        return view('admin.users.index', compact('users', 'roles'));
    }

    /** Карточка пользователя */
    public function show(User $user): View
    {
        $this->authorize('view', $user);
        $user->load(['managers', 'orders' => fn($q) => $q->latest()]);
        return view('admin.users.show', compact('user'));
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

        return redirect()->route('admin.users.show', $result['user'])
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

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Данные обновлены');
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
