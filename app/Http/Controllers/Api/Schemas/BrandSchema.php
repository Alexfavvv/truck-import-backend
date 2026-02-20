<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Brand',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/BrandShort'),
        new OA\Schema(properties: [
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(
                property: 'part_types',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/PartType')
            ),
            new OA\Property(
                property: 'truck_models',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/TruckModelShort')
            ),
        ]),
    ]
)]
class BrandSchema {}
