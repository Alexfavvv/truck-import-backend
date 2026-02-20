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
        return view('customer.profile', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name'  => 'nullable|string|max:255',
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|unique:users,email,' . $user->id,
            'company'    => 'nullable|string|max:255',
            'requisites' => 'nullable|string|max:2000',
            'position'   => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return back()->with('success', 'Профиль обновлён');
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

        return redirect()->route('customer.login')
            ->with('success', 'Аккаунт удалён');
    }
}
