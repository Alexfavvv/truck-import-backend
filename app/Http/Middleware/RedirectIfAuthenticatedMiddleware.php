<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user->isManagerTruck()) {
            return redirect()->route('manager.dashboard');
        }
        if ($user->isAdminTruck()) {
            return redirect()->route('admin.index');
        }
        return redirect()->route('customer.dashboard');
    }
}

