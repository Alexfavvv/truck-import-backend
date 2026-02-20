<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthTokenResponse',
    description: 'Ответ при успешной аутентификации',
    properties: [
        new OA\Property(
            property: 'token',
            type: 'string',
            example: '1|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890',
            description: 'Sanctum API-токен. Используйте как: Authorization: Bearer {token}'
        ),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'user', ref: '#/components/schemas/AuthUser'),
    ]
)]
class AuthTokenResponseSchema {}
