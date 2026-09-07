<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Client\BrandController;
use App\Http\Controllers\Api\V1\Client\CartController;
use App\Http\Controllers\Api\V1\Client\FeedbackController;
use App\Http\Controllers\Api\V1\Client\PostController;
use App\Http\Controllers\Api\V1\Client\PageController;
use App\Http\Controllers\Api\V1\Client\ProductController;
use App\Http\Controllers\Api\V1\Client\SiteOrderController;
use App\Http\Controllers\Api\V1\Client\TruckModelController;
use Illuminate\Support\Facades\Route;

// Аутентификация (Sanctum-токены)
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/sso/exchange', [AuthController::class, 'ssoExchange'])
        ->middleware('throttle:30,1');
    Route::get('/status', [AuthController::class, 'status'])
        ->middleware('throttle:120,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::prefix('v1/client')->group(function () {

    // Каталог
    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{brand}', [BrandController::class, 'show']);
    Route::get('/trucks/{truckModel}', [TruckModelController::class, 'show']);
    Route::get('/products/count', [ProductController::class, 'count']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Блог
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'show']);

    // Страницы (настройки/баннеры)
    Route::get('/pages/{slug}', [PageController::class, 'show']);

    // Почта
    Route::post('/send-feedback', [FeedbackController::class, 'send']);

    // Корзина и заказ с витрины (требует аутентификации)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/orders', [SiteOrderController::class, 'store'])
            ->middleware('throttle:30,1');
        Route::get('/cart', [CartController::class, 'show']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::post('/cart/items', [CartController::class, 'add']);
        Route::put('/cart/items/{cartItem}', [CartController::class, 'update']);
        Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove']);
    });
});
