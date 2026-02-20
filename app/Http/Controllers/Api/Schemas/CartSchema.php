<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Cart',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 5),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CartItem')
        ),
        new OA\Property(property: 'total', type: 'number', format: 'float', example: 7500.00),
        new OA\Property(property: 'items_count', type: 'integer', example: 3),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class CartSchema {}
