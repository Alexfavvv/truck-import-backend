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

class ProfileController extends Controller
{
    public function __construct(private readonly UserService $userService) {}
    public function show(): View
    {
        $user = Auth::user();
        $user->load('parent');

        $administratorName = null;
        if ($user->isManagerTruck()) {
            $admin = User::where('role', UserRole::AdminTruck->value)->first();
            $administratorName = $admin ? ($admin->full_name ?? $admin->name) : null;
        } elseif ($user->isManagerCustomer() && $user->parent) {
            $administratorName = $user->parent->full_name ?? $user->parent->name;
        }

        return view('admin.profile.show', compact('user', 'administratorName'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminTruck() && !$user->isAdminCustomer()) {
            abort(403, 'Изменение данных недоступно');
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'Данные сохранены');
    }

    public function updateAuth(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminTruck() && !$user->isAdminCustomer()) {
            abort(403, 'Изменение данных авторизации недоступно');
        }

        $rules = [
            'login'    => ['required', 'string', 'max:255', 'unique:users,login,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        $validated = $request->validate($rules);

        $data = ['login' => $validated['login']];
        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }
        $user->update($data);

        return back()->with('success', 'Данные авторизации сохранены');
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminCustomer()) {
            abort(403, 'Удаление аккаунта доступно только администратору заказчика');
        }

        Auth::logout();
        $this->userService->deleteAdminCustomer($user);
        $request->session()->invalidate();

        return redirect()->route('login')
            ->with('success', 'Аккаунт удалён');
    }
}
