<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthUser',
    description: 'Данные авторизованного пользователя',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 3),
        new OA\Property(property: 'login', type: 'string', example: 'company_ivanov'),
        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'ivanov@company.ru'),
        new OA\Property(
            property: 'role',
            type: 'string',
            enum: ['admin_truck', 'manager_truck', 'admin_customer', 'manager_customer'],
            example: 'admin_customer',
            description: 'Роль пользователя в системе'
        ),
        new OA\Property(
            property: 'role_label',
            type: 'string',
            example: 'Администратор Заказчика',
            description: 'Читаемое название роли'
        ),
        new OA\Property(property: 'full_name', type: 'string', nullable: true, example: 'Иванов Иван Иванович'),
        new OA\Property(property: 'company', type: 'string', nullable: true, example: 'ООО Ромашка'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+7 (999) 123-45-67'),
        new OA\Property(
            property: 'customer_id',
            type: 'string',
            nullable: true,
            example: 'TI-00042',
            description: 'ID заказчика (только для ролей admin_customer и manager_customer)'
        ),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ]
)]
class AuthUserSchema {}
