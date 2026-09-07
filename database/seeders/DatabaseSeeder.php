<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Отключаем проверку внешних ключей для массового удаления/создания
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            DB::statement('SET CONSTRAINTS ALL DEFERRED;');
            DB::statement('SET session_replication_role = replica;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        // Очищаем таблицы перед заполнением (опционально, но полезно для тестирования)
        $this->truncateTables([
            'posts',
            'characteristics',
            'metatags',
            'product_truck_manufacturer',
            'brand_part_type',
            'products',
            'truck_models',
            'brands',
            'part_types',
            'truck_manufacturers',
            'pages',
        ]);

        $this->call([
            UserSeeder::class,
            AdminTruckSeeder::class,
            PartTypeSeeder::class,
            BrandSeeder::class,
            TruckManufacturerSeeder::class,
            ProductSeeder::class,
            CharacteristicSeeder::class,
            TruckModelSeeder::class,
            PostSeeder::class,
            PageSeeder::class,
        ]);

        // Включаем проверку внешних ключей обратно
        if ($driver === 'pgsql') {
            DB::statement('SET CONSTRAINTS ALL IMMEDIATE;');
            DB::statement('SET session_replication_role = origin;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }

    /**
     * Очищает указанные таблицы.
     */
    protected function truncateTables(array $tables): void
    {
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
    }
}
