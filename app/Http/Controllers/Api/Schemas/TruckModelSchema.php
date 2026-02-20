<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TruckModel',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/TruckModelShort'),
        new OA\Schema(properties: [
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(
                property: 'brand',
                ref: '#/components/schemas/BrandShort'
            ),
        ]),
    ]
)]
class TruckModelSchema {}
