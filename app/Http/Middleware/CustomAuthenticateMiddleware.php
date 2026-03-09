<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomAuthenticateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Если не авторизован, перенаправляем в админскую форму логина
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}


