<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BrandController extends Controller
{
    #[OA\Get(
        path: '/v1/client/brands',
        summary: 'Список всех брендов',
        tags: ['Brands'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Массив брендов',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/BrandShort')
                )
            ),
        ]
    )]
    public function index(Request $request): array
    {
        return BrandResource::collection(Brand::all())->resolve();
    }

    #[OA\Get(
        path: '/v1/client/brands/{brand}',
        summary: 'Бренд с моделями и типами деталей',
        tags: ['Brands'],
        parameters: [
            new OA\Parameter(
                name: 'brand',
                in: 'path',
                required: true,
                description: 'Slug бренда',
                schema: new OA\Schema(type: 'string', example: 'man')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Бренд',
                content: new OA\JsonContent(ref: '#/components/schemas/Brand')
            ),
            new OA\Response(response: 404, description: 'Бренд не найден'),
        ]
    )]
    public function show(Brand $brand): array
    {
        $brand->load('partTypes', 'truckModels');
        return BrandResource::make($brand)->resolve();
    }
}
