<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerCompanyController extends Controller
{
    /**
     * Профиль компании админа заказчика: реквизиты + управление менеджерами (как в админке ТИ, в рамках своей компании).
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdminCustomer()) {
            return redirect()->route('customer.dashboard');
        }

        $user->loadCount('companyOrders');
        $user->load(['managers' => fn ($q) => $q->withCount('orders')]);

        $companyUsers = collect([
            (object)['user' => $user, 'role_label' => 'Администратор', 'orders_count' => $user->company_orders_count, 'is_admin' => true],
        ])->merge(
            $user->managers->map(fn ($m) => (object)['user' => $m, 'role_label' => 'Менеджер', 'orders_count' => $m->orders_count, 'is_admin' => false])
        );

        $search = $request->get('search');
        if ($search !== null && $search !== '') {
            $search = mb_strtolower($search);
            $companyUsers = $companyUsers->filter(function ($row) use ($search) {
                $u = $row->user;
                $name = mb_strtolower($u->full_name ?? $u->name ?? '');
                $customerId = mb_strtolower((string) ($u->customer_id ?? ''));
                return str_contains($name, $search) || str_contains($customerId, $search);
            });
        }

        return view('customer.company.show', [
            'user' => $user,
            'companyUsers' => $companyUsers,
        ]);
    }

    /**
     * Сохранение реквизитов своей компании.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdminCustomer()) {
            abort(403);
        }

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

        return redirect()->route('customer.company.show', ['tab' => 'requisites'])
            ->with('success', 'Изменения сохранены');
    }
}
