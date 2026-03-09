@extends('admin.layouts.main')

@section('content')
<div class="orders-page companies-page">
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
                    <a class="orders-page__tab-link {{ $tab === 'archive' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('admin.users.index', ['tab' => 'archive']) }}">
                        Архив компаний
                    </a>
                </li>
            </ul>
        </div>

        @if($tab === 'active')
            <div class="orders-page__filters-wrap">
                <form method="GET" class="orders-page__filters">
                    <input type="hidden" name="tab" value="active">
                    @can('create', \App\Models\User::class)
                        <span class="orders-page__btn orders-page__btn--company-create orders-page__btn--disabled" role="button" aria-disabled="true">
                            <i class="fas fa-plus"></i><span class="orders-page__btn-text">Создать профиль компании</span>
                        </span>
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                    <tr class="orders-page__row">
                                        <td class="orders-page__td orders-page__td--name">
                                            <a href="{{ route('admin.companies.show', $company) }}" class="orders-page__link">
                                                {{ $company->company ?: $company->full_name ?: '—' }}
                                            </a>
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
})();
</script>
@endsection
