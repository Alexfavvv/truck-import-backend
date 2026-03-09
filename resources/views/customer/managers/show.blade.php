@extends('customer.layouts.app')

@section('content')
@php
    $managerShowBase = route('customer.managers.show', $manager);
@endphp
<div class="profile-page manager-page">
    @if(session('success'))
        <div class="profile-page__alert profile-page__alert--success" role="alert">
            {{ session('success') }}
            <button type="button" class="profile-page__alert-close" data-dismiss="alert" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <a href="{{ route('customer.company.show', ['tab' => 'managers']) }}" class="profile-page__back manager-page__back">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> К списку всех менеджеров
    </a>

    <div class="profile-page__hero">
        <div class="profile-page__avatar">
            @php
                $name = $manager->full_name ?? $manager->name ?? '';
                $initial = mb_substr($name, 0, 1, 'UTF-8');
            @endphp
            <span>{{ $initial ?: '—' }}</span>
        </div>
        <div class="profile-page__hero-info">
            <div class="profile-page__hero-name">{{ $manager->full_name ?? $manager->name ?? '—' }}</div>
            @if($manager->customer_id)
            <div class="profile-page__hero-id-wrap">
                <span class="profile-page__hero-id">ID менеджера: {{ $manager->customer_id }}</span>
                <button type="button" class="profile-page__copy-btn" data-copy="{{ $manager->customer_id }}" aria-label="Скопировать ID">
                    <i class="fas fa-copy" aria-hidden="true"></i>
                </button>
            </div>
            @endif
        </div>
    </div>

    <div class="profile-page__card">
        <div class="profile-page__card-header">
            <div class="profile-page__tabs-dropdown">
                <select class="profile-page__tabs-select" data-manager-tab-select data-manager-tab-base="{{ $managerShowBase }}">
                    <option value="personal" {{ $tab === 'personal' ? 'selected' : '' }}>Личная информация</option>
                    <option value="auth" {{ $tab === 'auth' ? 'selected' : '' }}>Авторизация</option>
                    <option value="orders" {{ $tab === 'orders' ? 'selected' : '' }}>Заказы</option>
                    <option value="invoices" {{ $tab === 'invoices' ? 'selected' : '' }}>Счета</option>
                </select>
            </div>
            <ul class="profile-page__tabs">
                <li class="profile-page__tab">
                    <a class="profile-page__tab-link {{ $tab === 'personal' ? 'profile-page__tab-link--active' : '' }}"
                       href="{{ $managerShowBase }}?tab=personal">Личная информация</a>
                </li>
                <li class="profile-page__tab">
                    <a class="profile-page__tab-link {{ $tab === 'auth' ? 'profile-page__tab-link--active' : '' }}"
                       href="{{ $managerShowBase }}?tab=auth">Авторизация</a>
                </li>
                <li class="profile-page__tab">
                    <a class="profile-page__tab-link {{ $tab === 'orders' ? 'profile-page__tab-link--active' : '' }}"
                       href="{{ $managerShowBase }}?tab=orders">Заказы</a>
                </li>
                <li class="profile-page__tab">
                    <a class="profile-page__tab-link {{ $tab === 'invoices' ? 'profile-page__tab-link--active' : '' }}"
                       href="{{ $managerShowBase }}?tab=invoices">Счета</a>
                </li>
            </ul>
        </div>

        @if($tab === 'personal')
        <div class="profile-page__body">
            <h2 class="profile-page__section-title">Личная информация</h2>
            @if($canEdit)
            <form method="POST" action="{{ route('customer.managers.update', $manager) }}" class="profile-page__form">
                @csrf
                @method('PUT')
                <input type="hidden" name="_return_tab" value="personal">
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
                            <input type="text" name="full_name" class="profile-page__input profile-page__input--with-icon" value="{{ old('full_name', $manager->full_name ?? $manager->name) }}">
                            <i class="profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </label>
                    <label class="profile-page__field">
                        <span class="profile-page__label">Номер телефона</span>
                        <span class="profile-page__input-wrap">
                            <input type="text" name="phone" class="profile-page__input profile-page__input--with-icon" value="{{ old('phone', $manager->phone) }}">
                            <i class="profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </label>
                    <label class="profile-page__field">
                        <span class="profile-page__label">Email</span>
                        <span class="profile-page__input-wrap">
                            <input type="email" name="email" class="profile-page__input profile-page__input--with-icon" value="{{ old('email', $manager->email) }}">
                            <i class="profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </label>
                </div>
                <div class="profile-page__fields profile-page__fields--row2">
                    <div class="profile-page__field">
                        <span class="profile-page__label">Компания</span>
                        <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ optional($manager->parent)->company ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                    </div>
                </div>
                <div class="profile-page__actions">
                    <button type="submit" class="profile-page__btn profile-page__btn--save">Сохранить изменения</button>
                </div>
            </form>
            @else
            <div class="profile-page__fields profile-page__fields--row1">
                <div class="profile-page__field">
                    <span class="profile-page__label">ФИО</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $manager->full_name ?? $manager->name ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                </div>
                <div class="profile-page__field">
                    <span class="profile-page__label">Номер телефона</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $manager->phone ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                </div>
                <div class="profile-page__field">
                    <span class="profile-page__label">Email</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $manager->email ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
                </div>
            </div>
            <div class="profile-page__fields profile-page__fields--row2">
                <div class="profile-page__field">
                    <span class="profile-page__label">Компания</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ optional($manager->parent)->company ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
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

        @if($tab === 'auth')
        <div class="profile-page__body">
            <h2 class="profile-page__section-title">Авторизация</h2>
            @if($canEdit)
            <form id="manager-auth-form" method="POST" action="{{ route('customer.managers.update', $manager) }}" class="profile-page__form">
                @csrf
                @method('PUT')
                <input type="hidden" name="_return_tab" value="auth">
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
                            <input type="text" name="login" class="profile-page__input profile-page__input--with-icon" value="{{ old('login', $manager->login) }}" autocomplete="username">
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
            </form>
                <div class="profile-page__actions profile-page__actions--row">
                    <button type="submit" form="manager-auth-form" class="profile-page__btn profile-page__btn--save">Сохранить</button>
                    <form method="POST" action="{{ route('customer.managers.destroy', $manager) }}" class="profile-page__action-form" onsubmit="return confirm('Вы уверены, что хотите удалить этого менеджера? Все заказы будут сохранены.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="profile-page__btn profile-page__btn--reissue">Удалить</button>
                    </form>
                </div>
            @else
            <div class="profile-page__fields profile-page__fields--row1">
                <div class="profile-page__field">
                    <span class="profile-page__label">Логин</span>
                    <input type="text" class="profile-page__input profile-page__input--readonly" value="{{ $manager->login ?? '—' }}" readonly tabindex="-1" aria-readonly="true">
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

        @if($tab === 'orders')
        <div class="profile-page__body">
            <h2 class="profile-page__section-title">Заказы</h2>
            @if($ordersList->count() > 0)
            <div class="manager-page__table-wrap">
                <table class="manager-page__table">
                    <thead>
                        <tr>
                            <th>Номер</th>
                            <th>Статус</th>
                            <th>Сумма</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($ordersList as $order)
                        <tr>
                            <td>
                                <a href="{{ route('customer.orders.show', $order) }}" class="manager-page__link">{{ $order->order_number }}</a>
                            </td>
                            <td>
                                @php
                                    $badgeMod = $order->status === \App\Enums\OrderStatus::Completed ? 'completed' : ($order->status === \App\Enums\OrderStatus::Cancelled ? 'cancelled' : 'in-progress');
                                @endphp
                                <span class="orders-page__badge orders-page__badge--{{ $badgeMod }}">{{ $order->status->label() }}</span>
                            </td>
                            <td>{{ number_format($order->total, 2, '.', ' ') }} ₽</td>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="profile-page__muted">Заказов нет</p>
            @endif
        </div>
        @endif

        @if($tab === 'invoices')
        <div class="profile-page__body">
            <h2 class="profile-page__section-title">Счета</h2>
            @if($invoices->count() > 0)
            <div class="manager-page__table-wrap">
                <table class="manager-page__table">
                    <thead>
                        <tr>
                            <th>Номер счёта</th>
                            <th>Заказ</th>
                            <th>Сумма</th>
                            <th>Дата</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>
                                <a href="{{ route('customer.orders.show', $invoice->order) }}" class="manager-page__link">{{ $invoice->order->order_number }}</a>
                            </td>
                            <td>{{ number_format($invoice->amount ?? $invoice->order->total, 2, '.', ' ') }} ₽</td>
                            <td>{{ $invoice->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('customer.invoices.pdf', $invoice) }}" class="manager-page__btn manager-page__btn--pdf" target="_blank" rel="noopener noreferrer">
                                    Скачать PDF <i class="fas fa-download" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="profile-page__muted">Счетов нет</p>
            @endif
        </div>
        @endif
    </div>
</div>

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
    var sel = document.querySelector('[data-manager-tab-select]');
    if (sel) {
        sel.addEventListener('change', function() {
            var base = this.getAttribute('data-manager-tab-base') || '';
            var params = new URLSearchParams(window.location.search);
            params.set('tab', this.value);
            window.location.href = base + (params.toString() ? '?' + params.toString() : '');
        });
    }
})();
</script>
@endpush
@endsection
