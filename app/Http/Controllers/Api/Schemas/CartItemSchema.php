<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartItem',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'sku', type: 'string', example: 'MAN-FL-001'),
        new OA\Property(property: 'title', type: 'string', example: 'Фильтр масляный MAN'),
        new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true, example: 2500.00),
        new OA\Property(property: 'image_url', type: 'string', nullable: true),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 5000.00),
    ]
)]
class CartItemSchema {}
