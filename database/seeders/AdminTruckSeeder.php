<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminTruckSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::where('role', UserRole::AdminTruck->value)->first();

        if ($existing) {
            $existing->update([
                'login'    => 'admin',
                'password' => 'admin12345',
                'is_active' => true,
            ]);
            $this->command->info('Пароль администратора обновлён. Логин: admin, пароль: admin12345');
            return;
        }

        $admin = User::create([
            'name'      => 'Администратор Трак Импорт',
            'full_name' => 'Администратор Трак Импорт',
            'email'     => 'admin@truck-import.ru',
            'login'     => 'admin',
            'password'  => 'admin12345', // хешируется автоматически через cast в модели User
            'role'      => UserRole::AdminTruck,
            'is_admin'  => true,
            'is_active' => true,
            'company'   => 'Трак Импорт',
        ]);

        $this->command->info('Администратор создан:');
        $this->command->info('  Email:  ' . $admin->email);
        $this->command->info('  Логин:  admin');
        $this->command->info('  Пароль: admin12345  ← СМЕНИТЬ ПОСЛЕ ПЕРВОГО ВХОДА');
    }
}
