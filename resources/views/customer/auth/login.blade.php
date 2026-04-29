@extends('auth.layout')

@section('title', 'Трак Импорт — Вход в личный кабинет')

@section('content')
    <h1 class="auth-page__title">Вход в личный кабинет</h1>
    <p class="auth-page__subtitle">Личный кабинет заказчика — войдите в ваш аккаунт</p>

    @if(session('success'))
        <div class="auth-page__alert auth-page__alert--info">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->has('error'))
        <div class="auth-page__alert auth-page__alert--danger">
            {{ $errors->first('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('customer.login.post') }}">
        @csrf

        <div class="auth-page__input-group">
            <div class="input-group">
                <input type="text" name="login"
                       class="form-control auth-page__input @error('login') is-invalid @enderror"
                       placeholder="Логин или Email"
                       value="{{ old('login') }}" required autofocus autocomplete="username">
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-user"></span></div>
                </div>
            </div>
            @error('login')
                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="auth-page__input-group">
            <div class="input-group">
                <input type="password" name="password"
                       class="form-control auth-page__input @error('password') is-invalid @enderror"
                       placeholder="Пароль" required autocomplete="current-password">
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-lock"></span></div>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <label class="auth-page__checkbox-label">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Запомнить меня
            </label>
            <button type="submit" class="auth-page__btn auth-page__btn--block" style="max-width: 8rem;">
                Войти
            </button>
        </div>
    </form>

    <div class="auth-page__footer">
        <a href="{{ route('customer.register') }}" class="auth-page__link">
            <i class="fas fa-user-plus mr-1"></i>Зарегистрироваться
        </a>
    </div>
@endsection
