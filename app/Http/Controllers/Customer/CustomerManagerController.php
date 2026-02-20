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

    public function index(): View
    {
        $this->ensureAdminCustomer();
        $managers = Auth::user()->managers()->withCount('orders')->get();
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

    public function show(User $manager): View
    {
        $this->ensureAdminCustomer();
        $this->ensureOwns($manager);
        $manager->load(['orders' => fn($q) => $q->latest()]);
        return view('customer.managers.show', compact('manager'));
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
