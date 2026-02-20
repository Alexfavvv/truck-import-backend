<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsCustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('customer.login');
        }

        if (!$user->isCustomerSide()) {
            Auth::logout();
            return redirect()->route('customer.login')
                ->withErrors(['error' => 'Доступ только для заказчиков']);
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('customer.login')
                ->withErrors(['error' => 'Аккаунт деактивирован']);
        }

        return $next($request);
    }
}
