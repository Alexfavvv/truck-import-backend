<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Трак Импорт — Регистрация</title>
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE/dist/css/adminlte.min.css') }}">
    <style>
        .register-page { background: #f4f6f9; min-height: 100vh; padding: 40px 0; }
        .register-box { max-width: 600px; margin: 0 auto; padding: 0 16px; }
        .brand-logo { text-align: center; margin-bottom: 24px; }
        .brand-logo h1 { color: #1a3a5c; font-size: 28px; font-weight: bold; margin: 0; }
        .brand-logo p { color: #6b7280; font-size: 13px; margin: 4px 0 0; }
        .required { color: #ef4444; }
    </style>
</head>
<body class="register-page">
<div class="register-box">

    <div class="brand-logo">
        <h1><i class="fas fa-truck mr-2" style="color:#1a3a5c"></i>Трак Импорт</h1>
        <p>Регистрация администратора заказчика</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Создать аккаунт заказчика</h3>
            <div class="card-tools">
                <a href="{{ route('customer.login') }}" class="btn btn-sm btn-outline-secondary">
                    Уже есть аккаунт? Войти
                </a>
            </div>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('customer.register.post') }}">
                @csrf

                <h6 class="text-muted mb-3">
                    <i class="fas fa-user mr-1"></i>Личные данные
                </h6>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Телефон <span class="required">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type="text" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" placeholder="+7 (000) 000-00-00" required>
                                @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="email@example.com">
                                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>ФИО</label>
                            <input type="text" name="full_name"
                                   class="form-control @error('full_name') is-invalid @enderror"
                                   value="{{ old('full_name') }}" placeholder="Иванов Иван Иванович">
                            @error('full_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Должность</label>
                            <input type="text" name="position"
                                   class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position') }}" placeholder="Директор">
                            @error('position')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <hr>
                <h6 class="text-muted mb-3">
                    <i class="fas fa-building mr-1"></i>Данные компании
                </h6>

                <div class="form-group">
                    <label>Название компании</label>
                    <input type="text" name="company"
                           class="form-control @error('company') is-invalid @enderror"
                           value="{{ old('company') }}" placeholder='ООО "Название"'>
                    @error('company')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Реквизиты</label>
                    <textarea name="requisites" rows="3"
                              class="form-control @error('requisites') is-invalid @enderror"
                              placeholder="ИНН, КПП, юридический адрес...">{{ old('requisites') }}</textarea>
                    @error('requisites')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <hr>
                <h6 class="text-muted mb-3">
                    <i class="fas fa-lock mr-1"></i>Данные для входа
                </h6>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Логин <span class="required">*</span></label>
                            <input type="text" name="login"
                                   class="form-control @error('login') is-invalid @enderror"
                                   value="{{ old('login') }}" placeholder="my_company" required autocomplete="username">
                            @error('login')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @else
                                <small class="text-muted">Буквы, цифры, _ и -</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Пароль <span class="required">*</span></label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="new-password" placeholder="Минимум 8 символов">
                            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Повторите пароль <span class="required">*</span></label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    После регистрации вам будет автоматически присвоен уникальный <strong>ID заказчика</strong>.
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-user-plus mr-1"></i>Зарегистрироваться
                </button>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('AdminLTE/dist/js/adminlte.js') }}"></script>
</body>
</html>
