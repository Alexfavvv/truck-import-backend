<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin',
        'role', 'parent_id', 'is_active', 'approved_at', 'customer_id',
        'phone', 'company', 'requisites', 'full_name', 'position',
        'login',
        'inn', 'ogrn', 'bank_account', 'bank', 'bik', 'correspondent_account',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_admin'           => 'boolean',
        'is_active'          => 'boolean',
        'approved_at'        => 'datetime',
        'email_verified_at'  => 'datetime',
        'password'           => 'hashed',
        'role'               => UserRole::class,
    ];

    // ─── Проверки роли ───────────────────────────────────────────

    public function isAdminTruck(): bool
    {
        return $this->role === UserRole::AdminTruck;
    }

    public function isManagerTruck(): bool
    {
        return $this->role === UserRole::ManagerTruck;
    }

    public function isAdminCustomer(): bool
    {
        return $this->role === UserRole::AdminCustomer;
    }

    public function isManagerCustomer(): bool
    {
        return $this->role === UserRole::ManagerCustomer;
    }

    public function isTruckSide(): bool
    {
        return $this->role?->isTruckSide() ?? false;
    }

    public function isCustomerSide(): bool
    {
        return $this->role?->isCustomerSide() ?? false;
    }

    /** URL кабинета, используемый общей и клиентской шапками. */
    public function cabinetUrl(): string
    {
        if ($this->isAdminTruck()) {
            return route('admin.index');
        }

        if ($this->isManagerTruck()) {
            return route('manager.dashboard');
        }

        // Сохраняет текущий маршрут admin_customer, manager_customer и других клиентских ролей.
        return route('customer.dashboard');
    }

    /** Ожидает одобрения (самостоятельная регистрация, ещё не одобрен) */
    public function isPendingApproval(): bool
    {
        return $this->isAdminCustomer() && !$this->is_active && $this->approved_at === null;
    }

    /** Обратная совместимость */
    public function isAdmin(): bool
    {
        return $this->is_admin || $this->isTruckSide();
    }

    public function canChangeOrderStatus(): bool
    {
        return $this->isTruckSide();
    }

    public function canCreateInvoice(): bool
    {
        return $this->isAdminTruck();
    }

    public function canDeleteInvoice(): bool
    {
        return $this->isAdminTruck();
    }

    public function canChangeTruckInvoiceStatus(): bool
    {
        return $this->isTruckSide();
    }

    public function canChangeCustomerInvoiceStatus(): bool
    {
        return $this->isCustomerSide();
    }

    public function canDeleteOrder(): bool
    {
        return $this->isAdminTruck();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdminTruck();
    }

    // ─── Связи ───────────────────────────────────────────────────

    /** Родитель: admin_customer для manager_customer */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /** Подчинённые менеджеры (для admin_customer) */
    public function managers(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /** Заказы созданные этим пользователем */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /** Заказы компании (для admin_customer — свои + менеджеров) */
    public function companyOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'company_admin_id');
    }

    /** Компании заказчиков, закреплённые за менеджером Трак Импорт. */
    public function assignedCompanies(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'truck_manager_company', 'manager_id', 'company_admin_id')->withTimestamps();
    }

    public function managesCompany(int $companyAdminId): bool
    {
        return $this->isManagerTruck()
            && $this->assignedCompanies()->whereKey($companyAdminId)->exists();
    }

    /** Счета, созданные этим пользователем */
    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }
}
