<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function show($sku): array
    {
        $product = Product::where('sku', $sku)->firstOrFail();
        return ProductResource::make($product)->resolve();
    }
}
