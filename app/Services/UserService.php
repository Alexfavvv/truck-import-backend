<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Создать менеджера Трак Импорт (только admin_truck).
     */
    public function createManagerTruck(array $data): User
    {
        return User::create([
            'name'      => $data['full_name'] ?? $data['name'],
            'full_name' => $data['full_name'] ?? $data['name'],
            'email'     => $data['email'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'company'   => 'Трак Импорт',
            'login'     => $data['login'],
            'password'  => $data['password'],
            'role'      => UserRole::ManagerTruck,
            'is_admin'  => false,
            'is_active' => true,
        ]);
    }

    /**
     * Создать администратора заказчика (admin_truck или самостоятельная регистрация).
     *
     * @param  bool  $selfRegistered  true = самостоятельная регистрация (ожидает одобрения)
     */
    public function createAdminCustomer(array $data, bool $selfRegistered = false): User
    {
        $login    = $data['login'] ?? $this->generateLogin();
        $password = $data['password'] ?? $this->generatePassword();

        $user = User::create([
            'name'                 => $data['full_name'] ?? $data['name'] ?? $login,
            'full_name'            => $data['full_name'] ?? null,
            'email'                => $data['email'] ?? null,
            'phone'                => $data['phone'],
            'company'              => $data['company'] ?? null,
            'inn'                  => $data['inn'] ?? null,
            'ogrn'                 => $data['ogrn'] ?? null,
            'bank_account'         => $data['bank_account'] ?? null,
            'bank'                 => $data['bank'] ?? null,
            'bik'                  => $data['bik'] ?? null,
            'correspondent_account' => $data['correspondent_account'] ?? null,
            'position'             => $data['position'] ?? null,
            'login'                => $login,
            'password'             => $password,
            'role'                 => UserRole::AdminCustomer,
            'customer_id'          => $this->generateCustomerId(),
            'is_active'            => !$selfRegistered,
            'approved_at'           => $selfRegistered ? null : now(),
        ]);

        return $user;
    }

    /**
     * Создать менеджера заказчика (admin_customer или admin_truck).
     */
    public function createManagerCustomer(User $adminCustomer, array $data): array
    {
        $login    = $this->generateLogin();
        $rawPassword = $this->generatePassword();

        $manager = User::create([
            'name'      => $data['full_name'],
            'full_name' => $data['full_name'],
            'email'     => $data['email'] ?? null,
            'phone'     => $data['phone'],
            'login'     => $login,
            'password'  => $rawPassword,
            'role'      => UserRole::ManagerCustomer,
            'parent_id' => $adminCustomer->id,
            'is_active' => true,
        ]);

        // Возвращаем сырой пароль для отображения admin_customer
        return ['user' => $manager, 'password' => $rawPassword];
    }

    /**
     * Удалить admin_customer каскадно (+ все менеджеры + все заказы компании).
     */
    public function deleteAdminCustomer(User $adminCustomer): void
    {
        DB::transaction(function () use ($adminCustomer) {
            // Удалить заказы компании (включая заказы менеджеров)
            $adminCustomer->companyOrders()->delete();

            // Удалить всех менеджеров компании
            $adminCustomer->managers()->delete();

            // Удалить самого admin_customer
            $adminCustomer->delete();
        });
    }

    /**
     * Удалить менеджера заказчика (его заказы сохраняются).
     */
    public function deleteManagerCustomer(User $manager): void
    {
        // Заказы менеджера становятся «архивными» для admin_customer — company_admin_id остаётся
        $manager->delete();
    }

    /**
     * Удалить менеджера Трак Импорт (заказы передаются admin_truck).
     */
    public function deleteManagerTruck(User $manager): void
    {
        $adminTruck = User::where('role', UserRole::AdminTruck->value)->first();

        if ($adminTruck) {
            $manager->orders()->update(['user_id' => $adminTruck->id]);
        }

        $manager->delete();
    }

    private function generateLogin(): string
    {
        return 'user_' . strtolower(Str::random(8));
    }

    private function generatePassword(): string
    {
        return Str::random(10);
    }

    private function generateCustomerId(): string
    {
        return 'CUST-' . strtoupper(Str::random(8));
    }
}
