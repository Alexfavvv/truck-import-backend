<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login    = $request->input('login');
        $password = $request->input('password');

        // Поиск по логину или email
        $user = User::where('login', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors(['error' => 'Неверный логин или пароль'])->withInput();
        }

        if (!$user->isAdmin()) {
            return back()->withErrors(['error' => 'Доступ запрещён'])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors(['error' => 'Аккаунт деактивирован'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        return redirect()->route('admin.index');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
