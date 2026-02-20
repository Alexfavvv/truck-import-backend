<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function showRegisterForm(): View
    {
        if (Auth::check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|unique:users,email',
            'full_name'  => 'nullable|string|max:255',
            'company'    => 'nullable|string|max:255',
            'requisites' => 'nullable|string|max:2000',
            'position'   => 'nullable|string|max:255',
            'login'      => 'required|string|max:100|unique:users,login|alpha_dash',
            'password'   => 'required|string|min:8|confirmed',
        ], [
            'phone.required'    => 'Телефон обязателен',
            'login.required'    => 'Логин обязателен',
            'login.unique'      => 'Этот логин уже занят',
            'login.alpha_dash'  => 'Логин может содержать только буквы, цифры, дефис и подчёркивание',
            'password.required' => 'Пароль обязателен',
            'password.min'      => 'Пароль минимум 8 символов',
            'password.confirmed'=> 'Пароли не совпадают',
            'email.unique'      => 'Этот email уже используется',
        ]);

        $user = $this->userService->createAdminCustomer($validated);

        Auth::login($user);

        return redirect()->route('customer.dashboard')
            ->with('success', 'Добро пожаловать! Ваш ID заказчика: ' . $user->customer_id);
    }

    public function showLoginForm(): View
    {
        return view('customer.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login    = $request->input('login');
        $password = $request->input('password');

        $user = \App\Models\User::where('login', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return back()->withErrors(['error' => 'Неверный логин или пароль'])->withInput();
        }

        if (!$user->isCustomerSide()) {
            return back()->withErrors(['error' => 'Этот аккаунт не является аккаунтом заказчика'])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors(['error' => 'Аккаунт деактивирован'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login');
    }
}
