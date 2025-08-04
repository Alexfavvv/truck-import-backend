<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request): array
    {
        return BrandResource::collection(Brand::all())->resolve();
    }

    public function show(Brand $brand): array
    {
        $brand->load('partTypes');
        return BrandResource::make($brand)->resolve();
    }
}
