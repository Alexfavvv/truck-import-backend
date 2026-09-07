@extends('admin.layouts.app')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <strong>Трак Импорт</strong>
    </div>

    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Войдите в систему</p>

            @if($errors->has('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ $errors->first('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf

                <div class="input-group mb-3">
                    <input type="text"
                           name="login"
                           class="form-control @error('login') is-invalid @enderror"
                           placeholder="Логин или Email"
                           value="{{ old('login') }}"
                           required autofocus autocomplete="username">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                    @error('login')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Пароль"
                           required autocomplete="current-password">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-7">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Запомнить меня</label>
                        </div>
                    </div>
                    <div class="col-5">
                        <button type="submit" class="btn btn-primary btn-block">Войти</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
