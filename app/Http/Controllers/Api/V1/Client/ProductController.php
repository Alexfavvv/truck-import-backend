<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductShortResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): array
    {
        return ProductShortResource::collection(Product::all())->resolve();
    }

    public function show(Product $product): array
    {
        $product->load('truckManufacturers', 'characteristics', 'metatags');
        return ProductResource::make($product)->resolve();
    }
}
