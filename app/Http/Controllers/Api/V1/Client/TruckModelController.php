<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\TruckModel\TruckModelResource;
use App\Models\TruckModel;
use Illuminate\Http\Request;

class TruckModelController extends Controller
{
    public function index(Request $request): array
    {
        return TruckModelResource::collection(TruckModel::all())->resolve();
    }

    public function show(TruckModel $truckModel): array
    {
        return TruckModelResource::make($truckModel)->resolve();
    }
}
