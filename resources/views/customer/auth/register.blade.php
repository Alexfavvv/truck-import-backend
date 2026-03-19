@extends('auth.layout')

@section('title', 'Трак Импорт — Регистрация')
@section('card_class', 'auth-page__card--wide')

@section('content')
    <h1 class="auth-page__title">Регистрация</h1>
    <p class="auth-page__subtitle">Создайте аккаунт заказчика</p>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span></span>
        <a href="{{ route('login') }}" class="auth-page__link">
            <i class="fas fa-sign-in-alt mr-1"></i>Уже есть аккаунт? Войти
        </a>
    </div>

    @if ($errors->any())
        <div class="auth-page__alert auth-page__alert--danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.register.post') }}">
        @csrf

        <div class="auth-page__section-title">
            <i class="fas fa-user"></i> Личные данные
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>Телефон <span class="auth-page__required">*</span></label>
                    <div class="input-group">
                        <!-- <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        </div> -->
                        <input type="text" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="+7 (000) 000-00-00" required>
                        @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>Email</label>
                    <div class="input-group">
                        <!-- <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div> -->
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
                <div class="auth-page__form-group">
                    <label>ФИО</label>
                    <input type="text" name="full_name"
                           class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name') }}" placeholder="Иванов Иван Иванович">
                    @error('full_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>Должность</label>
                    <input type="text" name="position"
                           class="form-control @error('position') is-invalid @enderror"
                           value="{{ old('position') }}" placeholder="Директор">
                    @error('position')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="auth-page__divider"></div>
        <div class="auth-page__section-title">
            <i class="fas fa-building"></i> Данные компании
        </div>

        <div class="auth-page__form-group">
            <label>Название компании</label>
            <input type="text" name="company"
                   class="form-control @error('company') is-invalid @enderror"
                   value="{{ old('company') }}" placeholder='ООО "Название"'>
            @error('company')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="auth-page__section-title" style="margin-top: 1rem;">
            <i class="fas fa-file-invoice"></i> Реквизиты компании
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>ИНН</label>
                    <input type="text" name="inn"
                           class="form-control @error('inn') is-invalid @enderror"
                           value="{{ old('inn') }}" placeholder="7701234567">
                    @error('inn')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>ОГРН</label>
                    <input type="text" name="ogrn"
                           class="form-control @error('ogrn') is-invalid @enderror"
                           value="{{ old('ogrn') }}" placeholder="1234567890123">
                    @error('ogrn')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>Расчётный счёт</label>
                    <input type="text" name="bank_account"
                           class="form-control @error('bank_account') is-invalid @enderror"
                           value="{{ old('bank_account') }}" placeholder="40702810000000000000">
                    @error('bank_account')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>Банк</label>
                    <input type="text" name="bank"
                           class="form-control @error('bank') is-invalid @enderror"
                           value="{{ old('bank') }}" placeholder="ПАО Сбербанк">
                    @error('bank')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>БИК</label>
                    <input type="text" name="bik"
                           class="form-control @error('bik') is-invalid @enderror"
                           value="{{ old('bik') }}" placeholder="044525225">
                    @error('bik')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="auth-page__form-group">
                    <label>Корр. счёт</label>
                    <input type="text" name="correspondent_account"
                           class="form-control @error('correspondent_account') is-invalid @enderror"
                           value="{{ old('correspondent_account') }}" placeholder="30101810400000000225">
                    @error('correspondent_account')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="auth-page__divider"></div>
        <div class="auth-page__section-title">
            <i class="fas fa-lock"></i> Данные для входа
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="auth-page__form-group">
                    <label>Логин <span class="auth-page__required">*</span></label>
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
                <div class="auth-page__form-group">
                    <label>Пароль <span class="auth-page__required">*</span></label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required autocomplete="new-password" placeholder="Минимум 8 символов">
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="auth-page__form-group">
                    <label>Повторите пароль <span class="auth-page__required">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="form-control" required autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="auth-page__alert auth-page__alert--info mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            После регистрации вам будет автоматически присвоен уникальный <strong>ID заказчика</strong>.
        </div>

        <button type="submit" class="auth-page__btn auth-page__btn--block">
            <i class="fas fa-user-plus mr-1"></i>Зарегистрироваться
        </button>
    </form>
@endsection
