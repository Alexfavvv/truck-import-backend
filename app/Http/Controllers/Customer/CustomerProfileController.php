<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function show(): View
    {
        $user = Auth::user();
        $user->load('parent');
        $administratorName = $user->parent ? ($user->parent->full_name ?? $user->parent->name) : null;
        return view('customer.profile', compact('user', 'administratorName'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name'  => 'nullable|string|max:255',
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|unique:users,email,' . $user->id,
            'company'    => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return back()->with('success', 'Профиль обновлён');
    }

    public function updateAuth(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminCustomer()) {
            abort(403, 'Изменение данных авторизации доступно только администратору заказчика');
        }

        $validated = $request->validate([
            'login'    => ['required', 'string', 'max:255', 'unique:users,login,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = ['login' => $validated['login']];
        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }
        $user->update($data);

        return redirect()->route('customer.profile', ['tab' => 'auth'])->with('success', 'Данные авторизации сохранены');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminCustomer()) {
            abort(403, 'Только администратор заказчика может удалить аккаунт');
        }

        Auth::logout();
        $this->userService->deleteAdminCustomer($user);
        $request->session()->invalidate();

        return redirect()->route('login')
            ->with('success', 'Аккаунт удалён');
    }
}
