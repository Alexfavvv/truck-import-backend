<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'REST API магазина запчастей Truck Import.',
    title: 'Truck Import API',
    contact: new OA\Contact(name: 'Truck Import', email: 'info@truck-import.ru')
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'API сервер')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Токен Laravel Sanctum. Формат: Bearer {token}'
)]
#[OA\Tag(name: 'Auth', description: 'Аутентификация: регистрация, вход, выход, текущий пользователь')]
#[OA\Tag(name: 'Brands', description: 'Бренды производителей')]
#[OA\Tag(name: 'Products', description: 'Каталог запчастей')]
#[OA\Tag(name: 'TruckModels', description: 'Модели грузовиков')]
#[OA\Tag(name: 'Cart', description: 'Корзина (требует авторизации)')]
#[OA\Tag(name: 'Feedback', description: 'Обратная связь')]
class OpenApiController
{
}
