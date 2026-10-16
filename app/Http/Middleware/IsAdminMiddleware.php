<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdminTruck()) {
            if ($user?->isManagerTruck()) {
                abort(403, 'Раздел доступен только администратору.');
            }
            return redirect()->route('login')->withErrors(['error' => 'Доступ запрещён']);
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['error' => 'Аккаунт деактивирован']);
        }

        return $next($request);
    }
}

