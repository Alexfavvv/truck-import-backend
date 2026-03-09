<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return redirect()->route('login')->withErrors(['error' => 'Доступ запрещён']);
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['error' => 'Аккаунт деактивирован']);
        }

        return $next($request);
    }
}

