@extends('admin.layouts.main')

@section('content')
<div class="orders-page companies-page admin-compact-page admin-compact-page--companies">
    @if(session('success'))
        <div class="orders-page__alert orders-page__alert--success" role="alert">
            {{ session('success') }}
            <button type="button" class="orders-page__alert-close" data-dismiss="alert" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <header class="orders-page__section-header">
        <h1 class="orders-page__title">Все компании</h1>
    </header>

    <div class="orders-page__card">
        <div class="orders-page__card-header">
            <div class="orders-page__tabs-dropdown">
                <select class="orders-page__tabs-select" data-orders-tab-select data-orders-tab-base="{{ route('admin.users.index') }}">
                    <option value="active" {{ $tab === 'active' ? 'selected' : '' }}>Активные профили</option>
                    <option value="pending" {{ $tab === 'pending' ? 'selected' : '' }}>Ожидают одобрения</option>
                    <option value="archive" {{ $tab === 'archive' ? 'selected' : '' }}>Архив компаний</option>
                </select>
            </div>
            <ul class="orders-page__tabs">
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'active' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('admin.users.index', ['tab' => 'active']) }}">
                        Активные профили
                    </a>
                </li>
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'pending' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('admin.users.index', ['tab' => 'pending']) }}">
                        Ожидают одобрения
                    </a>
                </li>
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'archive' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('admin.users.index', ['tab' => 'archive']) }}">
                        Архив компаний
                    </a>
                </li>
            </ul>
        </div>

        @if($tab === 'active' || $tab === 'pending')
            <div class="orders-page__filters-wrap">
                <form method="GET" class="orders-page__filters">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    @can('create', \App\Models\User::class)
                        <button type="button" class="orders-page__btn orders-page__btn--company-create"
                                data-toggle="modal" data-target="#createCompanyModal">
                            <i class="fas fa-plus"></i><span class="orders-page__btn-text">Создать профиль компании</span>
                        </button>
                    @endcan
                    <div class="orders-page__search-group">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="orders-page__search-input"
                               placeholder="Поиск по компаниям">
                        <button type="submit" class="orders-page__search-submit" aria-label="Искать">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="orders-page__body">
                @if($companies->isNotEmpty())
                    <div class="orders-page__table-wrap">
                        <table class="orders-page__table">
                            <thead>
                                <tr class="orders-page__row orders-page__row--head">
                                    <th class="orders-page__th orders-page__th--name">Наименование</th>
                                    <th class="orders-page__th orders-page__th--admin">Администратор</th>
                                    <th class="orders-page__th orders-page__th--managers">Всего менеджеров</th>
                                    <th class="orders-page__th orders-page__th--orders">Всего заказов</th>
                                    @if($tab === 'pending')
                                    <th class="orders-page__th">Действия</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                    <tr class="orders-page__row">
                                        <td class="orders-page__td orders-page__td--name">
                                            <a href="{{ route('admin.companies.show', $company) }}" class="orders-page__link">
                                                {{ $company->company ?: $company->full_name ?: '—' }}
                                            </a>
                                            @if($tab === 'pending')
                                                <span class="badge badge-warning ml-1">Ожидает одобрения</span>
                                            @endif
                                        </td>
                                        <td class="orders-page__td orders-page__td--admin">
                                            <a href="{{ route('admin.users.show', $company) }}" class="orders-page__link">
                                                {{ $company->full_name ?: '—' }}
                                            </a>
                                        </td>
                                        <td class="orders-page__td orders-page__td--managers">
                                            {{ $company->managers_count ?? 0 }}
                                        </td>
                                        <td class="orders-page__td orders-page__td--orders">
                                            {{ $company->company_orders_count ?? 0 }}
                                        </td>
                                        @if($tab === 'pending')
                                        <td class="orders-page__td">
                                            @if(auth()->user()?->canManageUsers())
                                                <form method="POST" action="{{ route('admin.users.approve', $company) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check mr-1"></i>Одобрить
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary btn-sm ml-1">
                                                    <i class="fas fa-eye mr-1"></i>Просмотр
                                                </a>
                                            @else
                                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-eye mr-1"></i>Просмотр
                                                </a>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($companies->hasPages())
                        <div class="orders-page__pagination">
                            {{ $companies->links() }}
                        </div>
                    @endif
                @else
                    <p class="orders-page__empty">Компании не найдены</p>
                @endif
            </div>
        @else
            <div class="orders-page__body">
                <p class="orders-page__empty orders-page__empty--message">Страница в разработке</p>
            </div>
        @endif
    </div>
</div>

@can('create', \App\Models\User::class)
<div class="modal fade auth-modal-wrap" id="createCompanyModal" tabindex="-1" role="dialog" aria-labelledby="createCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg auth-modal" role="document">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <input type="hidden" name="role" value="admin_customer">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCompanyModalLabel">Создать профиль компании</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="auth-page__alert auth-page__alert--danger">
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="auth-page__section-title"><i class="fas fa-user"></i> Личные данные</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>ФИО <span class="auth-page__required">*</span></label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name') }}" required placeholder="Иванов Иван Иванович">
                                @error('full_name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Должность</label>
                                <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
                                       value="{{ old('position') }}" placeholder="Директор">
                                @error('position')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Телефон <span class="auth-page__required">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required placeholder="+7 (000) 000-00-00">
                                @error('phone')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="email@example.com">
                                @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="auth-page__section-title auth-page__section-title--spaced"><i class="fas fa-building"></i> Данные компании</div>
                    <div class="form-group auth-page__form-group">
                        <label>Название компании</label>
                        <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
                               value="{{ old('company') }}" placeholder='ООО "Название"'>
                        @error('company')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-page__section-title auth-page__section-title--spaced"><i class="fas fa-file-invoice"></i> Реквизиты</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>ИНН</label>
                                <input type="text" name="inn" class="form-control @error('inn') is-invalid @enderror"
                                       value="{{ old('inn') }}" placeholder="7701234567">
                                @error('inn')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>ОГРН</label>
                                <input type="text" name="ogrn" class="form-control @error('ogrn') is-invalid @enderror"
                                       value="{{ old('ogrn') }}" placeholder="1234567890123">
                                @error('ogrn')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Расчётный счёт</label>
                                <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                                       value="{{ old('bank_account') }}" placeholder="40702810000000000000">
                                @error('bank_account')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Банк</label>
                                <input type="text" name="bank" class="form-control @error('bank') is-invalid @enderror"
                                       value="{{ old('bank') }}" placeholder="ПАО Сбербанк">
                                @error('bank')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>БИК</label>
                                <input type="text" name="bik" class="form-control @error('bik') is-invalid @enderror"
                                       value="{{ old('bik') }}" placeholder="044525225">
                                @error('bik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Корр. счёт</label>
                                <input type="text" name="correspondent_account" class="form-control @error('correspondent_account') is-invalid @enderror"
                                       value="{{ old('correspondent_account') }}" placeholder="30101810400000000225">
                                @error('correspondent_account')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="auth-page__section-title auth-page__section-title--spaced"><i class="fas fa-lock"></i> Данные для входа</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Логин</label>
                                <input type="text" name="login" class="form-control @error('login') is-invalid @enderror"
                                       value="{{ old('login') }}" placeholder="Оставьте пустым — сгенерируется автоматически">
                                @error('login')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group auth-page__form-group">
                                <label>Пароль</label>
                                <input type="text" name="password" class="form-control @error('password') is-invalid @enderror"
                                       value="{{ old('password') }}" placeholder="Оставьте пустым — сгенерируется автоматически">
                                @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer auth-modal__footer">
                    <button type="button" class="auth-modal__btn auth-modal__btn--ghost" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="auth-page__btn">
                        <i class="fas fa-plus mr-1"></i>Создать компанию
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

<script>
(function() {
    var sel = document.querySelector('[data-orders-tab-select]');
    if (sel) {
        sel.addEventListener('change', function() {
            var base = this.getAttribute('data-orders-tab-base') || '';
            var params = new URLSearchParams(window.location.search);
            params.set('tab', this.value);
            params.delete('search');
            window.location.href = base + (params.toString() ? '?' + params.toString() : '');
        });
    }

    // Открыть модалку при ошибках валидации (редирект с формы)
    var createModal = document.getElementById('createCompanyModal');
    var hasErrors = document.querySelector('#createCompanyModal .auth-page__alert--danger');
    if (createModal && hasErrors) {
        $(createModal).modal('show');
    }
})();
</script>
@endsection
