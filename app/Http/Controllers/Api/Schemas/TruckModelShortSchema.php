<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TruckModelShort',
    properties: [
        new OA\Property(property: 'slug', type: 'string', example: 'man-tgx'),
        new OA\Property(property: 'name', type: 'string', example: 'MAN TGX'),
    ]
)]
class TruckModelShortSchema {}
