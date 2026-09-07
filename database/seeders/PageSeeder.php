<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'name' => 'Главная страница',
                'settings' => [
                    'banners' => [
                        [
                            'id' => 1,
                            'name' => 'Левый главный баннер (большой)',
                            'image_pc' => null,
                            'image_tablet' => null,
                            'image_mobile' => null,
                            'link_type' => 'callback', // 'link' or 'callback'
                            'link_url' => '',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Правый верхний баннер',
                            'image_pc' => null,
                            'image_tablet' => null,
                            'image_mobile' => null,
                            'link_type' => 'link',
                            'link_url' => '/catalog',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Правый нижний баннер',
                            'image_pc' => null,
                            'image_tablet' => null,
                            'image_mobile' => null,
                            'link_type' => 'link',
                            'link_url' => '/contacts',
                        ],
                    ]
                ]
            ]
        );

        Page::updateOrCreate(
            ['slug' => 'site_settings'],
            [
                'name' => 'Настройки сайта',
                'settings' => [
                    'messengers' => [
                        'max_link' => 'https://max.mail.ru',
                        'whatsapp_link' => 'https://wa.me/79006044614',
                        'telegram_link' => 'https://t.me/truck_import',
                    ]
                ]
            ]
        );
    }
}
