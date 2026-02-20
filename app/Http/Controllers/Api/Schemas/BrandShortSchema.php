<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BrandShort',
    properties: [
        new OA\Property(property: 'slug', type: 'string', example: 'man'),
        new OA\Property(property: 'name', type: 'string', example: 'MAN'),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'https://example.com/man.png'),
    ]
)]
class BrandShortSchema {}
