<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Policies\InvoicePolicy;
use App\Policies\OrderPolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Order::class   => OrderPolicy::class,
        Invoice::class => InvoicePolicy::class,
        User::class    => UserPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
        Model::unguard();
        Paginator::useBootstrapFive();

        // За прокси (Nginx) Laravel не видит HTTPS — принудительно используем схему из APP_URL
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
