<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The quantity field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['quantity' => ['The quantity field is required.']]
        ),
    ]
)]
class ValidationErrorSchema {}
