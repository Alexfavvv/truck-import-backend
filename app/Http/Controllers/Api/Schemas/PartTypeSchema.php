<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PartType',
    properties: [
        new OA\Property(property: 'slug', type: 'string', example: 'engine-parts'),
        new OA\Property(property: 'name', type: 'string', example: 'Детали двигателя'),
    ]
)]
class PartTypeSchema {}
