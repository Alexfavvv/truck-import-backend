<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TruckManagerController extends Controller
{
    public function index(): View
    {
        $managers = User::where('role', UserRole::ManagerTruck->value)
            ->with('assignedCompanies')->orderBy('full_name')->paginate(20);

        return view('admin.truck-managers.index', compact('managers'));
    }

    public function create(): View
    {
        $companies = $this->companies();
        return view('admin.truck-managers.form', compact('companies'));
    }

    public function store(Request $request, UserService $users): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $this->ensureLoginDoesNotMatchEmail($data['login']);

        DB::transaction(function () use ($data, $users) {
            $manager = $users->createManagerTruck($data);
            $manager->assignedCompanies()->sync($data['company_ids'] ?? []);
        });

        return redirect()->route('admin.truck-managers.index')->with('success', 'Менеджер создан.');
    }

    public function edit(User $manager): View
    {
        $this->ensureManager($manager);
        $manager->load('assignedCompanies');
        $companies = $this->companies();
        return view('admin.truck-managers.form', compact('manager', 'companies'));
    }

    public function update(Request $request, User $manager): RedirectResponse
    {
        $this->ensureManager($manager);
        $data = $request->validate($this->rules($manager));
        $this->ensureLoginDoesNotMatchEmail($data['login'], $manager);

        DB::transaction(function () use ($manager, $data) {
            $changes = [
                'name' => $data['full_name'],
                'full_name' => $data['full_name'],
                'login' => $data['login'],
                'is_active' => $data['is_active'],
            ];
            if (!empty($data['password'])) {
                $changes['password'] = $data['password'];
            }
            $manager->update($changes);
            $manager->assignedCompanies()->sync($data['company_ids'] ?? []);
            if (!$manager->is_active) {
                $manager->tokens()->delete();
            }
        });

        return redirect()->route('admin.truck-managers.edit', $manager)->with('success', 'Данные менеджера сохранены.');
    }

    private function rules(?User $manager = null): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:100', Rule::unique('users', 'login')->ignore($manager?->id)],
            'password' => [$manager ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => [$manager ? 'required' : 'sometimes', 'boolean'],
            'company_ids' => ['sometimes', 'array'],
            'company_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('role', UserRole::AdminCustomer->value)],
        ];
    }

    private function companies()
    {
        return User::where('role', UserRole::AdminCustomer->value)
            ->orderBy('company')->get(['id', 'company', 'full_name', 'customer_id']);
    }

    private function ensureManager(User $manager): void
    {
        abort_unless($manager->isManagerTruck(), 404);
    }

    private function ensureLoginDoesNotMatchEmail(string $login, ?User $manager = null): void
    {
        $query = User::where('email', $login);
        if ($manager) {
            $query->where('id', '!=', $manager->id);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['login' => 'Этот логин уже используется как email другого пользователя.']);
        }
    }
}
