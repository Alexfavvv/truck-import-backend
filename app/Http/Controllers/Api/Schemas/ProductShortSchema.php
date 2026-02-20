<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductShort',
    properties: [
        new OA\Property(property: 'sku', type: 'string', example: 'MAN-FL-001'),
        new OA\Property(property: 'title', type: 'string', example: 'Фильтр масляный MAN'),
        new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true, example: 2500.00),
        new OA\Property(property: 'quantity', type: 'integer', nullable: true, example: 10),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'https://example.com/part.jpg'),
    ]
)]
class ProductShortSchema {}
