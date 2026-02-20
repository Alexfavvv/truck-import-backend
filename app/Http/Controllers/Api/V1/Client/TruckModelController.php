<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\TruckModel\TruckModelResource;
use App\Models\TruckModel;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TruckModelController extends Controller
{
    #[OA\Get(
        path: '/v1/client/trucks/{truckModel}',
        summary: 'Данные модели грузовика',
        tags: ['TruckModels'],
        parameters: [
            new OA\Parameter(
                name: 'truckModel',
                in: 'path',
                required: true,
                description: 'Slug модели грузовика',
                schema: new OA\Schema(type: 'string', example: 'man-tgx')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Модель грузовика',
                content: new OA\JsonContent(ref: '#/components/schemas/TruckModel')
            ),
            new OA\Response(response: 404, description: 'Модель не найдена'),
        ]
    )]
    public function show(TruckModel $truckModel): array
    {
        return TruckModelResource::make($truckModel)->resolve();
    }
}
