@extends('admin.layouts.main')

@section('content')
@php
    $tab = request()->get('tab', 'requisites');
    $tab = in_array($tab, ['requisites', 'managers'], true) ? $tab : 'requisites';
    $companyName = $user->company ?: $user->full_name ?? $user->name ?? '—';
@endphp
<div class="company-profile-page">
    @if(session('success'))
        <div class="company-profile-page__alert company-profile-page__alert--success" role="alert">
            {{ session('success') }}
            <button type="button" class="company-profile-page__alert-close" data-dismiss="alert" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <a href="{{ route('admin.users.index') }}" class="company-profile-page__back">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> К списку всех компаний
    </a>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="company-profile-page__title mb-0">Профиль компании {{ $companyName }}</h1>
        @if($user->isPendingApproval() && auth()->user()?->canManageUsers())
            <form method="POST" action="{{ route('admin.users.approve', $user) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check mr-1"></i>Одобрить заявку
                </button>
            </form>
        @endif
    </div>

    <div class="company-profile-page__card">
        <div class="company-profile-page__card-header">
            <div class="company-profile-page__tabs-dropdown">
                <select class="company-profile-page__tabs-select" data-company-tab-select data-company-tab-base="{{ route('admin.companies.show', $user) }}">
                    <option value="requisites" {{ $tab === 'requisites' ? 'selected' : '' }}>Реквизиты компании</option>
                    <option value="managers" {{ $tab === 'managers' ? 'selected' : '' }}>Управление менеджерами</option>
                </select>
            </div>
            <ul class="company-profile-page__tabs">
                <li class="company-profile-page__tab">
                    <a class="company-profile-page__tab-link {{ $tab === 'requisites' ? 'company-profile-page__tab-link--active' : '' }}"
                       href="{{ route('admin.companies.show', [$user, 'tab' => 'requisites']) }}">
                        Реквизиты компании
                    </a>
                </li>
                <li class="company-profile-page__tab">
                    <a class="company-profile-page__tab-link {{ $tab === 'managers' ? 'company-profile-page__tab-link--active' : '' }}"
                       href="{{ route('admin.companies.show', [$user, 'tab' => 'managers']) }}">
                        Управление менеджерами
                    </a>
                </li>
            </ul>
        </div>

        @if($tab === 'requisites')
        <div class="company-profile-page__body">
            <h2 class="company-profile-page__section-title">Реквизиты компании</h2>
            @can('update', $user)
            <form method="POST" action="{{ route('admin.companies.update', $user) }}" class="company-profile-page__form">
                @csrf
                @method('PUT')
                @if($errors->any())
                    <ul class="company-profile-page__errors">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="company-profile-page__fields">
                    <div class="company-profile-page__field">
                        <label class="company-profile-page__label" for="company">Наименование</label>
                        <span class="company-profile-page__input-wrap">
                            <input type="text" id="company" name="company" class="company-profile-page__input company-profile-page__input--with-icon"
                                   value="{{ old('company', $user->company) }}" placeholder="—">
                            <i class="company-profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="company-profile-page__field">
                        <label class="company-profile-page__label" for="inn">ИНН</label>
                        <span class="company-profile-page__input-wrap">
                            <input type="text" id="inn" name="inn" class="company-profile-page__input company-profile-page__input--with-icon"
                                   value="{{ old('inn', $user->inn) }}" placeholder="—">
                            <i class="company-profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="company-profile-page__field">
                        <label class="company-profile-page__label" for="ogrn">ОГРН</label>
                        <span class="company-profile-page__input-wrap">
                            <input type="text" id="ogrn" name="ogrn" class="company-profile-page__input company-profile-page__input--with-icon"
                                   value="{{ old('ogrn', $user->ogrn) }}" placeholder="—">
                            <i class="company-profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="company-profile-page__field">
                        <label class="company-profile-page__label" for="bank_account">Расчетный счет</label>
                        <span class="company-profile-page__input-wrap">
                            <input type="text" id="bank_account" name="bank_account" class="company-profile-page__input company-profile-page__input--with-icon"
                                   value="{{ old('bank_account', $user->bank_account) }}" placeholder="—">
                            <i class="company-profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="company-profile-page__field">
                        <label class="company-profile-page__label" for="bank">Банк</label>
                        <span class="company-profile-page__input-wrap">
                            <input type="text" id="bank" name="bank" class="company-profile-page__input company-profile-page__input--with-icon"
                                   value="{{ old('bank', $user->bank) }}" placeholder="—">
                            <i class="company-profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="company-profile-page__field">
                        <label class="company-profile-page__label" for="bik">БИК</label>
                        <span class="company-profile-page__input-wrap">
                            <input type="text" id="bik" name="bik" class="company-profile-page__input company-profile-page__input--with-icon"
                                   value="{{ old('bik', $user->bik) }}" placeholder="—">
                            <i class="company-profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="company-profile-page__field">
                        <label class="company-profile-page__label" for="correspondent_account">Кор. счет</label>
                        <span class="company-profile-page__input-wrap">
                            <input type="text" id="correspondent_account" name="correspondent_account" class="company-profile-page__input company-profile-page__input--with-icon"
                                   value="{{ old('correspondent_account', $user->correspondent_account) }}" placeholder="—">
                            <i class="company-profile-page__input-icon fas fa-pencil-alt" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
                <div class="company-profile-page__actions">
                    <button type="submit" class="company-profile-page__btn company-profile-page__btn--save">Сохранить изменения</button>
                </div>
            </form>
            @else
            <div class="company-profile-page__fields company-profile-page__fields--readonly">
                <div class="company-profile-page__field">
                    <span class="company-profile-page__label">Наименование</span>
                    <span class="company-profile-page__value">{{ $user->company ?: '—' }}</span>
                </div>
                <div class="company-profile-page__field">
                    <span class="company-profile-page__label">ИНН</span>
                    <span class="company-profile-page__value">{{ $user->inn ?: '—' }}</span>
                </div>
                <div class="company-profile-page__field">
                    <span class="company-profile-page__label">ОГРН</span>
                    <span class="company-profile-page__value">{{ $user->ogrn ?: '—' }}</span>
                </div>
                <div class="company-profile-page__field">
                    <span class="company-profile-page__label">Расчетный счет</span>
                    <span class="company-profile-page__value">{{ $user->bank_account ?: '—' }}</span>
                </div>
                <div class="company-profile-page__field">
                    <span class="company-profile-page__label">Банк</span>
                    <span class="company-profile-page__value">{{ $user->bank ?: '—' }}</span>
                </div>
                <div class="company-profile-page__field">
                    <span class="company-profile-page__label">БИК</span>
                    <span class="company-profile-page__value">{{ $user->bik ?: '—' }}</span>
                </div>
                <div class="company-profile-page__field">
                    <span class="company-profile-page__label">Кор. счет</span>
                    <span class="company-profile-page__value">{{ $user->correspondent_account ?: '—' }}</span>
                </div>
            </div>
            @endcan
        </div>
        @else
        <div class="company-profile-page__body">
            <h2 class="company-profile-page__section-title">Все пользователи компании</h2>
            <div class="company-profile-page__toolbar">
                @can('create', \App\Models\User::class)
                <span class="company-profile-page__btn company-profile-page__btn--company-create company-profile-page__btn--disabled" role="button" aria-disabled="true">
                    <i class="fas fa-plus" aria-hidden="true"></i> Добавить пользователя
                </span>
                @endcan
                <form method="GET" class="company-profile-page__search-form" action="{{ route('admin.companies.show', $user) }}">
                    <input type="hidden" name="tab" value="managers">
                    <div class="company-profile-page__search-group">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="company-profile-page__search-input" placeholder="Поиск">
                        <button type="submit" class="company-profile-page__search-submit" aria-label="Искать">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>
            @if($companyUsers->isNotEmpty())
            <div class="company-profile-page__table-wrap">
                <table class="company-profile-page__table">
                    <thead>
                        <tr>
                            <th>ФИО</th>
                            <th>Должность</th>
                            <th>ID Менеджера</th>
                            <th>Всего заказов</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($companyUsers as $row)
                        <tr>
                            <td>
                                <a href="{{ route('admin.users.show', $row->user) }}" class="company-profile-page__link">
                                    {{ $row->user->full_name ?? $row->user->name ?? '—' }}
                                </a>
                            </td>
                            <td>{{ $row->role_label }}</td>
                            <td>{{ $row->user->customer_id ?? '—' }}</td>
                            <td>{{ $row->orders_count ?? 0 }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="company-profile-page__empty">Пользователи не найдены</p>
            @endif
        </div>
        @endif
    </div>
</div>

<script>
(function() {
    var sel = document.querySelector('[data-company-tab-select]');
    if (sel) {
        sel.addEventListener('change', function() {
            var base = this.getAttribute('data-company-tab-base') || '';
            var params = new URLSearchParams(window.location.search);
            params.set('tab', this.value);
            window.location.href = base + (params.toString() ? '?' + params.toString() : '');
        });
    }
})();
</script>
@endsection
