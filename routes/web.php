<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PartSearchController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PartTypeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TruckManufacturerController;
use App\Http\Controllers\Admin\TruckModelController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UnifiedAuthController;
use App\Http\Middleware\CustomAuthenticateMiddleware;
use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Middleware\RedirectIfAuthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

// Корень сайта — редирект на страницу входа
Route::get('/', fn () => redirect()->route('login'))->name('home');

// Доступ в админку — только для авторизованного пользователя с ролью
Route::middleware([CustomAuthenticateMiddleware::class, IsAdminMiddleware::class])->group(function () {
    Route::get('/admin', function () { return view('admin.main.index'); })->name('admin.index');

    // ── Поиск деталей (отдельная страница) ───────────────────────────
    Route::get('/admin/search', [PartSearchController::class, 'index'])->name('admin.search.index');

    // ── Корзина (добавление из админки в корзину текущего пользователя) ─
    Route::post('/admin/cart/add', [CartController::class, 'add'])->name('admin.cart.add');

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

    // ── Мой профиль ─────────────────────────────────────────────────
    Route::get('/admin/profile', [ProfileController::class, 'show'])->name('admin.profile');
    Route::put('/admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/admin/profile/auth', [ProfileController::class, 'updateAuth'])->name('admin.profile.auth.update');
    Route::delete('/admin/profile', [ProfileController::class, 'destroyAccount'])->name('admin.profile.destroy');

    // ── Пользователи (admin_truck + admin_customer) ──────────────────
    Route::resource('/admin/users', UserController::class)
        ->names('admin.users');

    // ── Профиль компании (карточка компании, не админа) ─────────────
    Route::get('/admin/companies/{user}', [UserController::class, 'showCompany'])
        ->name('admin.companies.show');
    Route::put('/admin/companies/{user}', [UserController::class, 'updateCompany'])
        ->name('admin.companies.update');
});

// Единая страница входа (трак импорт и заказчики). Редирект по роли после входа.
Route::middleware([RedirectIfAuthenticatedMiddleware::class])->group(function () {
    Route::get('/login', [UnifiedAuthController::class, 'showLoginForm'])->name('login');
});
Route::post('/login', [UnifiedAuthController::class, 'login'])->name('login.post');

// Обратная совместимость: старый URL админки ведёт на общую страницу входа
Route::get('/admin/login', fn () => redirect()->route('login'))->name('admin.login');

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// ── Кабинет заказчика ─────────────────────────────────────────────────────
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerInvoiceController;
use App\Http\Controllers\Customer\CustomerCompanyController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerManagerController;
use App\Http\Controllers\Customer\CustomerSearchController;
use App\Http\Middleware\IsCustomerMiddleware;

// Публичные страницы заказчика (без авторизации)
Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.post');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

// Защищённые страницы кабинета заказчика
Route::middleware([IsCustomerMiddleware::class])->prefix('cabinet')->name('customer.')->group(function () {
    Route::get('/', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Поиск деталей
    Route::get('/search', [CustomerSearchController::class, 'index'])->name('search.index');

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
    Route::put('/profile/auth', [CustomerProfileController::class, 'updateAuth'])->name('profile.auth.update');
    Route::delete('/profile', [CustomerProfileController::class, 'destroy'])->name('profile.destroy');

    // Профиль компании (реквизиты + менеджеры, только admin_customer)
    Route::get('/company', [CustomerCompanyController::class, 'show'])->name('company.show');
    Route::put('/company', [CustomerCompanyController::class, 'update'])->name('company.update');

    // Менеджеры (только admin_customer)
    Route::get('/managers', [CustomerManagerController::class, 'index'])->name('managers.index');
    Route::get('/managers/create', [CustomerManagerController::class, 'create'])->name('managers.create');
    Route::post('/managers', [CustomerManagerController::class, 'store'])->name('managers.store');
    Route::get('/managers/{manager}', [CustomerManagerController::class, 'show'])->name('managers.show');
    Route::put('/managers/{manager}', [CustomerManagerController::class, 'update'])->name('managers.update');
    Route::delete('/managers/{manager}', [CustomerManagerController::class, 'destroy'])->name('managers.destroy');
});

// Временный отладочный маршрут (удалить после проверки): при APP_DEBUG=true откройте /_debug-routes в браузере
if (config('app.debug')) {
    Route::get('/_debug-routes', function () {
        return response()->json([
            'customer.search.index' => route('customer.search.index'),
            'admin.search.index'    => route('admin.search.index'),
        ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    });
}
