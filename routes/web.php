<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PartTypeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TruckManufacturerController;
use App\Http\Controllers\Admin\TruckModelController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Middleware\CustomAuthenticateMiddleware;
use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Middleware\RedirectIfAuthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

// Доступ в админку — только для авторизованного пользователя с ролью
Route::middleware([CustomAuthenticateMiddleware::class, IsAdminMiddleware::class])->group(function () {
    Route::get('/admin', function () { return view('admin.main.index'); })->name('admin.index');

    // ── Каталог (существующие разделы) ──────────────────────────────
    Route::resource('/admin/brands', BrandController::class)->names('admin.brands')->except('show');
    Route::resource('/admin/truck-models', TruckModelController::class)->names('admin.truck-models')->except('show');
    Route::resource('/admin/truck-manufacturers', TruckManufacturerController::class)->names('admin.truck-manufacturers')->except('show');
    Route::resource('/admin/part-types', PartTypeController::class)->names('admin.part-types')->except('show');
    Route::resource('/admin/products', ProductController::class)->names('admin.products')->except('show');

    Route::get('admin/products/import', [ProductController::class, 'showImportForm'])->name('admin.products.import.form');
    Route::post('admin/products/import', [ProductController::class, 'import'])->name('admin.products.import');
    Route::get('admin/products/import-status', [ProductController::class, 'getImportStatus'])->name('admin.products.import.status');

    // ── Заказы (все роли с разными правами) ─────────────────────────
    Route::resource('/admin/orders', OrderController::class)
        ->names('admin.orders')
        ->except(['edit', 'update']);
    Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('admin.orders.status');

    // ── Счета ────────────────────────────────────────────────────────
    Route::resource('/admin/invoices', InvoiceController::class)
        ->names('admin.invoices')
        ->except(['edit', 'update']);
    Route::patch('/admin/invoices/{invoice}/truck-status', [InvoiceController::class, 'updateTruckStatus'])
        ->name('admin.invoices.truck-status');
    Route::patch('/admin/invoices/{invoice}/customer-status', [InvoiceController::class, 'updateCustomerStatus'])
        ->name('admin.invoices.customer-status');
    Route::get('/admin/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
        ->name('admin.invoices.pdf');

    // ── Пользователи (admin_truck + admin_customer) ──────────────────
    Route::resource('/admin/users', UserController::class)
        ->names('admin.users');
});

// Вход и выход в админку (без ограничений)
Route::middleware([RedirectIfAuthenticatedMiddleware::class])->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
});
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// ── Кабинет заказчика ─────────────────────────────────────────────────────
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerInvoiceController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerManagerController;
use App\Http\Middleware\IsCustomerMiddleware;

// Публичные страницы заказчика (без авторизации)
Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.post');
Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login.post');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

// Защищённые страницы кабинета заказчика
Route::middleware([IsCustomerMiddleware::class])->prefix('cabinet')->name('customer.')->group(function () {
    Route::get('/', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Заказы
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');

    // Счета
    Route::get('/invoices', [CustomerInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [CustomerInvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('/invoices/{invoice}/status', [CustomerInvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::get('/invoices/{invoice}/pdf', [CustomerInvoiceController::class, 'pdf'])->name('invoices.pdf');

    // Профиль
    Route::get('/profile', [CustomerProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [CustomerProfileController::class, 'destroy'])->name('profile.destroy');

    // Менеджеры (только admin_customer)
    Route::get('/managers', [CustomerManagerController::class, 'index'])->name('managers.index');
    Route::get('/managers/create', [CustomerManagerController::class, 'create'])->name('managers.create');
    Route::post('/managers', [CustomerManagerController::class, 'store'])->name('managers.store');
    Route::get('/managers/{manager}', [CustomerManagerController::class, 'show'])->name('managers.show');
    Route::delete('/managers/{manager}', [CustomerManagerController::class, 'destroy'])->name('managers.destroy');
});
