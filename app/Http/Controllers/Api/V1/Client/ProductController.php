<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Filters\ProductFilter;
use App\Http\Requests\Api\V1\Client\Product\IndexRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductShortResource;
use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/v1/client/products',
        summary: 'Каталог запчастей с фильтрами',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'brand',
                in: 'query',
                required: false,
                description: 'Slug бренда',
                schema: new OA\Schema(type: 'string', example: 'man')
            ),
            new OA\Parameter(
                name: 'model',
                in: 'query',
                required: false,
                description: 'Slug модели грузовика',
                schema: new OA\Schema(type: 'string', example: 'man-tgx')
            ),
            new OA\Parameter(
                name: 'part',
                in: 'query',
                required: false,
                description: 'Slug типа детали',
                schema: new OA\Schema(type: 'string', example: 'engine-parts')
            ),
            new OA\Parameter(
                name: 'sku',
                in: 'query',
                required: false,
                description: 'Артикул (поиск по вхождению)',
                schema: new OA\Schema(type: 'string', example: 'FL-001')
            ),
            new OA\Parameter(
                name: 'price',
                in: 'query',
                required: false,
                description: 'Диапазон цены в формате "min,max"',
                schema: new OA\Schema(type: 'string', example: '1000,5000')
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: 'Количество на странице',
                schema: new OA\Schema(type: 'integer', example: 20)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Номер страницы',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список запчастей',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductShort')
                )
            ),
        ]
    )]
    public function index(IndexRequest $request): array
    {
        $data = $request->validated();
        $posts = Product::query();
        $posts = (new ProductFilter())->apply($data, $posts);
        $posts = $posts->get();
        return ProductShortResource::collection($posts)->resolve();
    }

    #[OA\Get(
        path: '/v1/client/products/{product}',
        summary: 'Детальная страница запчасти',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                in: 'path',
                required: true,
                description: 'ID запчасти',
                schema: new OA\Schema(type: 'integer', example: 42)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Запчасть',
                content: new OA\JsonContent(ref: '#/components/schemas/Product')
            ),
            new OA\Response(response: 404, description: 'Запчасть не найдена'),
        ]
    )]
    public function show(Product $product): array
    {
        $product->load('truckManufacturers', 'characteristics', 'metatags');
        return ProductResource::make($product)->resolve();
    }
}
