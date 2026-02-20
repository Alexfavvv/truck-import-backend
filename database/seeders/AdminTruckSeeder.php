<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminTruckSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::where('role', UserRole::AdminTruck->value)->first();

        if ($existing) {
            $this->command->info('Администратор Трак Импорт уже существует: ' . $existing->email);
            return;
        }

        $admin = User::create([
            'name'      => 'Администратор Трак Импорт',
            'full_name' => 'Администратор Трак Импорт',
            'email'     => 'admin@truck-import.ru',
            'login'     => 'admin',
            'password'  => Hash::make('admin12345'),
            'role'      => UserRole::AdminTruck,
            'is_admin'  => true,
            'is_active' => true,
        ]);

        $this->command->info('Администратор создан:');
        $this->command->info('  Email:  ' . $admin->email);
        $this->command->info('  Логин:  admin');
        $this->command->info('  Пароль: admin12345  ← СМЕНИТЬ ПОСЛЕ ПЕРВОГО ВХОДА');
    }
}
