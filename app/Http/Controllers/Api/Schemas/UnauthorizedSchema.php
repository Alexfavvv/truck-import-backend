<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Unauthorized',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ]
)]
class UnauthorizedSchema {}
