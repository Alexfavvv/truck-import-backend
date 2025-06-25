<?php


use App\Http\Controllers\Api\V1\Client\CategoryController;
use App\Http\Controllers\Api\V1\Client\ProductController;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::prefix('v1/client')->group(function () {
    Route::get('/categories/{brandSlug}', [CategoryController::class, 'showBrand'])->name('v1.client.categories.brand.show');
    Route::get('/categories/{brandSlug}/models/{modelSlug}', [CategoryController::class, 'showModel'])->name('v1.client.categories.model.show');
    Route::get('/categories/{brandSlug}/parts/{partSlug}', [CategoryController::class, 'showPart'])->name('v1.client.categories.part.show');
    Route::get('/products/{sku}', [ProductController::class, 'show'])->name('v1.client.products.show');
});
