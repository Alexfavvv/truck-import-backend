<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Middleware\CustomAuthenticateMiddleware;
use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Middleware\RedirectIfAuthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

// Доступ в админку — только для авторизованного админа
Route::middleware([CustomAuthenticateMiddleware::class, IsAdminMiddleware::class])->group(function () {
    Route::get('/admin', function () {return view('admin.main.index');})->name('admin.index');

    //CRUD
    Route::resource('/admin/brands', BrandController::class)->names('admin.brands')->except('show');
    Route::resource('/admin/categories', CategoryController::class)->names('admin.categories')->except('show');
    Route::resource('/admin/products', ProductController::class)->names('admin.products')->except('show');

    // import products
    Route::get('admin/products/import', [ProductController::class, 'showImportForm'])->name('admin.products.import.form');
    Route::post('admin/products/import', [ProductController::class, 'import'])->name('admin.products.import');
});

// Вход и выход (без ограничений)
Route::middleware([RedirectIfAuthenticatedMiddleware::class])->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
});
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
