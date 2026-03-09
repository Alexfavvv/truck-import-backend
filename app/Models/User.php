<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin',
        'role', 'parent_id', 'is_active', 'customer_id',
        'phone', 'company', 'requisites', 'full_name', 'position',
        'login',
        'inn', 'ogrn', 'bank_account', 'bank', 'bik', 'correspondent_account',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_admin'           => 'boolean',
        'is_active'          => 'boolean',
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

    /** Счета, созданные этим пользователем */
    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }
}
