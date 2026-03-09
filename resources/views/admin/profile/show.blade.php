@extends('admin.layouts.main')

@section('content')
@php
    $tab = request()->get('tab', 'personal');
    $tab = $tab === 'auth' ? 'auth' : 'personal';
    $user = auth()->user();
    $canEdit = $user->isAdminTruck() || $user->isAdminCustomer();
    $showNotice = $user->isManagerTruck() || $user->isManagerCustomer();
@endphp
<div class="profile-page">
    @if(session('success'))
        <div class="profile-page__alert profile-page__alert--success" role="alert">
            {{ session('success') }}
            <button type="button" class="profile-page__alert-close" data-dismiss="alert" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <h1 class="profile-page__main-title">Мой профиль</h1>

    <div class="profile-page__card">
        <div class="profile-page__card-header">
            <div class="profile-page__tabs-dropdown">
                <select class="profile-page__tabs-select"
                        data-profile-tab-select
                        data-profile-tab-base="{{ route('admin.profile') }}">
                    <option value="personal" {{ $tab === 'personal' ? 'selected' : '' }}>Личная информация</option>
                    <option value="auth" {{ $tab === 'auth' ? 'selected' : '' }}>Авторизация</option>
                </select>
            </div>
            <ul class="profile-page__tabs">
                <li class="profile-page__tab">
                    <a class="profile-page__tab-link {{ $tab === 'personal' ? 'profile-page__tab-link--active' : '' }}"
                       href="{{ route('admin.profile', ['tab' => 'personal']) }}">
                        Личная информация
                    </a>
                </li>
                <li class="profile-page__tab">
                    <a class="profile-page__tab-link {{ $tab === 'auth' ? 'profile-page__tab-link--active' : '' }}"
                       href="{{ route('admin.profile', ['tab' => 'auth']) }}">
                        Авторизация
                    </a>
                </li>
            </ul>
        </div>

        @if($tab === 'personal')
        <div class="profile-page__body">
            <h2 class="profile-page__section-title">Личная информация</h2>

            <div class="profile-page__hero">
                <div class="profile-page__avatar">
                    @php
                        $name = $user->full_name ?? $user->name ?? '';
                        $initial = mb_substr($name, 0, 1, 'UTF-8');
                    @endphp
                    <span>{{ $initial }}</span>
                </div>
                <div class="profile-page__hero-info">
                    <div class="profile-page__hero-name">{{ $user->full_name ?? $user->name ?? '—' }}</div>
                    @if($user->customer_id)
                        <div class="profile-page__hero-id-wrap">
                            <span class="profile-page__hero-id">ID заказчика: {{ $user->customer_id }}</span>
                            <button type="button"
                                    class="profile-page__copy-btn"
                                    data-copy="{{ $user->customer_id }}"
                                    aria-label="Скопировать ID">
                                <i class="fas fa-copy" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            @if($canEdit)
            <form method="POST" action="{{ route('admin.profile.update') }}" class="profile-page__form">
                @csrf
                @method('PUT')
                @if($errors->any())
                    <ul class="profile-page__errors">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="profile-page__fields profile-page__fields--row1">
                    <label class="profile-page__field">
                        <span class="profile-page__label">ФИО</span>
                        <span class="profile-page__input-wrap">
                            <input type="text" name="full_name" class="profile-page__input profile-page__input--with-icon" value="{{ old('full_name', $user->full_name) }}">
                            <i class="profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </label>
                    <label class="profile-page__field">
                        <span class="profile-page__label">Номер телефона</span>
                        <span class="profile-page__input-wrap">
                            <input type="text" name="phone" class="profile-page__input profile-page__input--with-icon" value="{{ old('phone', $user->phone) }}">
                            <i class="profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </label>
                    <label class="profile-page__field">
                        <span class="profile-page__label">Email</span>
                        <span class="profile-page__input-wrap">
                            <input type="email" name="email" class="profile-page__input profile-page__input--with-icon" value="{{ old('email', $user->email) }}">
                            <i class="profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </label>
                </div>
                @if($user->isAdminCustomer())
                <div class="profile-page__fields profile-page__fields--row2">
                    <div class="profile-page__field">
                        <span class="profile-page__label">Компания</span>
                        <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $user->company ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                    </div>
                </div>
                @endif
                <div class="profile-page__actions">
                    <button type="submit" class="profile-page__btn profile-page__btn--save">Сохранить изменения</button>
                    <a href="{{ route('admin.logout') }}" class="profile-page__btn profile-page__btn--primary"
                       onclick="event.preventDefault(); document.getElementById('profile-logout-form').submit();">Выйти из профиля</a>
                </div>
            </form>
            <form id="profile-logout-form" action="{{ route('admin.logout') }}" method="POST" class="profile-page__hidden-form">
                @csrf
            </form>
            @else
            <div class="profile-page__fields profile-page__fields--row1">
                <div class="profile-page__field">
                    <span class="profile-page__label">ФИО</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $user->full_name ?? $user->name ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                </div>
                <div class="profile-page__field">
                    <span class="profile-page__label">Номер телефона</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $user->phone ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                </div>
                <div class="profile-page__field">
                    <span class="profile-page__label">Email</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $user->email ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                </div>
            </div>
            <div class="profile-page__fields profile-page__fields--row2">
                <div class="profile-page__field">
                    <span class="profile-page__label">Компания</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $user->company ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                </div>
                @if(isset($administratorName))
                <div class="profile-page__field">
                    <span class="profile-page__label">Ваш администратор</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $administratorName }}" readonly tabindex="-1" aria-readonly="true">
                </div>
                @endif
            </div>
            @if($showNotice)
            <div class="profile-page__notice">
                <span class="profile-page__notice-icon" aria-hidden="true">
                    <span class="profile-page__notice-icon-circle"></span>
                    <span class="profile-page__notice-icon-mark">&#161;</span>
                </span>
                <p class="profile-page__notice-text">Для изменения данных обратитесь к Вашему администратору.</p>
            </div>
            @endif
            <div class="profile-page__actions">
                <a href="{{ route('admin.logout') }}" class="profile-page__btn profile-page__btn--primary"
                   onclick="event.preventDefault(); document.getElementById('profile-logout-form').submit();">Выйти из профиля</a>
            </div>
            <form id="profile-logout-form" action="{{ route('admin.logout') }}" method="POST" class="profile-page__hidden-form">
                @csrf
            </form>
            @endif
        </div>
        @endif

        @if($tab === 'auth')
        <div class="profile-page__body">
            <h2 class="profile-page__section-title">Авторизация</h2>

            @if($canEdit)
            <form method="POST" action="{{ route('admin.profile.auth.update') }}" class="profile-page__form">
                @csrf
                @method('PUT')
                @if($errors->any())
                    <ul class="profile-page__errors">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="profile-page__fields profile-page__fields--row1">
                    <label class="profile-page__field">
                        <span class="profile-page__label">Логин</span>
                        <span class="profile-page__input-wrap">
                            <input type="text" name="login" class="profile-page__input profile-page__input--with-icon" value="{{ old('login', $user->login) }}" autocomplete="username">
                            <i class="profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </label>
                    <label class="profile-page__field">
                        <span class="profile-page__label">Пароль</span>
                        <input type="password" name="password" class="profile-page__input" placeholder="Оставьте пустым, чтобы не менять" autocomplete="new-password">
                    </label>
                    <label class="profile-page__field">
                        <span class="profile-page__label">Подтверждение пароля</span>
                        <input type="password" name="password_confirmation" class="profile-page__input" placeholder="Повторите новый пароль" autocomplete="new-password">
                    </label>
                </div>
                <div class="profile-page__actions">
                    <button type="submit" class="profile-page__btn profile-page__btn--save">Сохранить</button>
                    @if($user->isAdminCustomer())
                        <button type="button" class="profile-page__btn profile-page__btn--danger" data-profile-delete-account aria-label="Удалить аккаунт">
                            Удалить
                        </button>
                    @endif
                </div>
            </form>
            @if($user->isAdminCustomer())
            <form id="profile-delete-account-form" action="{{ route('admin.profile.destroy') }}" method="POST" class="profile-page__hidden-form">
                @csrf
                @method('DELETE')
            </form>
            @endif
            @else
            <div class="profile-page__fields profile-page__fields--row1">
                <div class="profile-page__field">
                    <span class="profile-page__label">Логин</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $user->login }}" readonly tabindex="-1" aria-readonly="true">
                </div>
                <div class="profile-page__field">
                    <span class="profile-page__label">Пароль</span>
                    <input type="password" class="profile-page__input profile-page__input--readonly" value="••••••••" readonly tabindex="-1" aria-readonly="true" autocomplete="off">
                </div>
            </div>
            <div class="profile-page__notice">
                <span class="profile-page__notice-icon" aria-hidden="true">
                    <span class="profile-page__notice-icon-circle"></span>
                    <span class="profile-page__notice-icon-mark">&#161;</span>
                </span>
                <p class="profile-page__notice-text">Для изменения данных обратитесь к Вашему администратору.</p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    document.querySelectorAll('.profile-page__copy-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var value = this.getAttribute('data-copy');
            if (!value) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(function() {
                    var icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-copy');
                        icon.classList.add('fa-check');
                        setTimeout(function() {
                            icon.classList.remove('fa-check');
                            icon.classList.add('fa-copy');
                        }, 1500);
                    }
                });
            }
        });
    });
})();
(function() {
    var sel = document.querySelector('[data-profile-tab-select]');
    if (sel) {
        sel.addEventListener('change', function() {
            var base = this.getAttribute('data-profile-tab-base') || '';
            var params = new URLSearchParams(window.location.search);
            params.set('tab', this.value);
            window.location.href = base + (params.toString() ? '?' + params.toString() : '');
        });
    }
})();
(function() {
    var delBtn = document.querySelector('[data-profile-delete-account]');
    var form = document.getElementById('profile-delete-account-form');
    if (delBtn && form) {
        delBtn.addEventListener('click', function() {
            if (confirm('Вы уверены, что хотите удалить аккаунт? Это действие необратимо.')) {
                form.submit();
            }
        });
    }
})();
</script>
@endpush
