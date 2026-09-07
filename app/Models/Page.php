<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value with on-the-fly creation.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($value === 'site_settings') {
            return $this->where('slug', 'site_settings')->firstOrCreate([
                'slug' => 'site_settings'
            ], [
                'name' => 'Настройки сайта',
                'settings' => [
                    'messengers' => [
                        'max_link' => 'https://max.mail.ru',
                        'whatsapp_link' => 'https://wa.me/79006044614',
                        'telegram_link' => 'https://t.me/truck_import',
                    ]
                ]
            ]);
        }

        if ($value === 'home') {
            return $this->where('slug', 'home')->firstOrCreate([
                'slug' => 'home'
            ], [
                'name' => 'Главная страница',
                'settings' => [
                    'banners' => [
                        [
                            'id' => 1,
                            'name' => 'Левый главный баннер (большой)',
                            'image_pc' => null,
                            'image_tablet' => null,
                            'image_mobile' => null,
                            'link_type' => 'callback',
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
            ]);
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
