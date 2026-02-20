<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Проверяет, что авторизованный пользователь имеет одну из переданных ролей.
     *
     * Пример использования в роутах:
     *   ->middleware('role:admin_truck')
     *   ->middleware('role:admin_truck,manager_truck')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/admin/login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->role?->value === $role) {
                return $next($request);
            }
        }

        abort(403, 'Недостаточно прав доступа');
    }
}
