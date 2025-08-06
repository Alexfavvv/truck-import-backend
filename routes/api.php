<?php


use App\Http\Controllers\Api\V1\Client\FeedbackController;
use App\Http\Controllers\Api\V1\Client\TruckModelController;
use App\Http\Controllers\Api\V1\Client\BrandController;
use App\Http\Controllers\Api\V1\Client\ProductController;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::prefix('v1/client')->group(function () {

    // Каталог
    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{brand}', [BrandController::class, 'show']);
    Route::get('/trucks/{truckModel}', [TruckModelController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);


    // Почта
    Route::post('/send-feedback', [FeedbackController::class, 'send']);
});
