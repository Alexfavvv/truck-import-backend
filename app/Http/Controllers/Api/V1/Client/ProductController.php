<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Filters\ProductFilter;
use App\Http\Requests\Api\V1\Client\Product\IndexRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductShortResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(IndexRequest $request): array
    {
        $data = $request->validated();
        $posts = Product::query();
        $posts = (new ProductFilter())->apply($data, $posts);
        $posts = $posts->get();
        return ProductShortResource::collection($posts)->resolve();
    }

    public function show(Product $product): array
    {
        $product->load('truckManufacturers', 'characteristics', 'metatags');
        return ProductResource::make($product)->resolve();
    }
}
