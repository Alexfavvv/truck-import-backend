<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UnifiedAuthController extends Controller
{
    /**
     * Единая страница входа для трак импорта и заказчиков.
     * Если уже авторизован — редирект в нужный раздел по роли.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Обработка входа. Редирект в админку или кабинет заказчика в зависимости от роли.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        $user = User::where('login', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withErrors(['error' => 'Неверный логин или пароль'])->withInput();
        }

        if (! $user->is_active) {
            $message = $user->isPendingApproval()
                ? 'Ваш аккаунт ожидает одобрения администратором Трак Импорт.'
                : 'Аккаунт деактивирован';

            return back()->withErrors(['error' => $message])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        if ($user->isManagerTruck()) {
            $intended = (string) $request->session()->get('url.intended', '');
            $path = parse_url($intended, PHP_URL_PATH);
            if ($path === '/manager' || str_starts_with((string) $path, '/manager/') || $path === '/sso/authorize') {
                return redirect()->intended(route('manager.dashboard'));
            }
            $request->session()->forget('url.intended');
            return redirect()->route('manager.dashboard');
        }

        $fallback = $user->isAdminTruck() ? route('admin.index') : route('customer.dashboard');
        return redirect()->intended($fallback);
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        if ($user->isManagerTruck()) {
            return redirect()->route('manager.dashboard');
        }

        if ($user->isAdminTruck()) {
            return redirect()->route('admin.index');
        }

        return redirect()->route('customer.dashboard');
    }
}
