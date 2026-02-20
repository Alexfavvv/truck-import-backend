<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Product',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/ProductShort'),
        new OA\Schema(properties: [
            new OA\Property(property: 'id', type: 'integer', example: 42),
            new OA\Property(property: 'brand_id', type: 'integer', example: 1),
            new OA\Property(property: 'part_type_id', type: 'integer', nullable: true, example: 3),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'delivery', type: 'string', nullable: true, example: '7-14 дней'),
            new OA\Property(
                property: 'truck_manufacturers',
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'MAN'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Property(
                property: 'characteristics',
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Вязкость'),
                        new OA\Property(property: 'value', type: 'string', example: '5W-40'),
                    ],
                    type: 'object'
                )
            ),
        ]),
    ]
)]
class ProductSchema {}
