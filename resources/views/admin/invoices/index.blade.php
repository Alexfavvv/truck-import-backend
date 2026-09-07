@extends('admin.layouts.main')

@section('content')
<div class="orders-page">
    @if(session('success'))
        <div class="orders-page__alert orders-page__alert--success" role="alert">
            {{ session('success') }}
            <button type="button" class="orders-page__alert-close" data-dismiss="alert" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="orders-page__card">
        <div class="orders-page__card-header">
            <div class="orders-page__tabs-dropdown">
                <select class="orders-page__tabs-select" data-invoices-tab-select data-invoices-tab-base="{{ route('admin.invoices.index') }}">
                    <option value="my" {{ $tab === 'my' ? 'selected' : '' }}>Мои счета</option>
                    <option value="company" {{ $tab === 'company' ? 'selected' : '' }}>Счета компаний</option>
                    <option value="create" {{ $tab === 'create' ? 'selected' : '' }}>Выставить счет</option>
                </select>
            </div>
            <ul class="orders-page__tabs">
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'my' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('admin.invoices.index', ['tab' => 'my']) }}">
                        Мои счета
                    </a>
                </li>
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'company' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('admin.invoices.index', ['tab' => 'company']) }}">
                        Счета компаний
                    </a>
                </li>
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'create' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('admin.invoices.index', ['tab' => 'create']) }}">
                        Выставить счет
                    </a>
                </li>
            </ul>
        </div>

        @if($tab !== 'create')
        <header class="orders-page__section-header">
            <h1 class="orders-page__title">
                @if($tab === 'my')
                    Мои счета
                @else
                    Счета компаний
                @endif
            </h1>
        </header>
        @endif

        @if($tab === 'create')
        <div class="orders-page__notice">
            <span class="orders-page__notice-icon" aria-hidden="true">
                <span class="orders-page__notice-icon-circle"></span>
                <span class="orders-page__notice-icon-mark">&#161;</span>
            </span>
            <p class="orders-page__notice-text">
                Здесь перечислены активные заказы, для которых требуется выставление счета. <br>Чтобы выставить счет, перейдите в заказ и нажмите на кнопку «Выставить счет».
            </p>
        </div>
        @endif

        <div class="orders-page__filters-wrap">
            <form method="GET" class="orders-page__filters">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($tab === 'my' || $tab === 'company')
                    <select name="status" class="orders-page__select" data-orders-filter-auto-submit>
                        <option value="">Все статусы</option>
                        @foreach($statusOptions as $option)
                            <option value="{{ $option['value'] }}" {{ (string)request('status') === (string)$option['value'] ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if(($tab === 'my' || $tab === 'company') && $companies->isNotEmpty())
                    <select name="company_id" class="orders-page__select" data-orders-filter-auto-submit>
                        <option value="">Все компании</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ (string)request('company_id') === (string)$company->id ? 'selected' : '' }}>
                                {{ $company->company ?? $company->full_name ?? $company->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if(($tab === 'my' || $tab === 'company') && $managers->isNotEmpty())
                    <select name="manager_id" class="orders-page__select" data-orders-filter-auto-submit>
                        <option value="">Все менеджеры</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ (string)request('manager_id') === (string)$manager->id ? 'selected' : '' }}>
                                {{ $manager->full_name ?? $manager->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if($tab === 'create' && $companies->isNotEmpty())
                    <select name="company_id" class="orders-page__select" data-orders-filter-auto-submit>
                        <option value="">Все компании</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ (string)request('company_id') === (string)$company->id ? 'selected' : '' }}>
                                {{ $company->company ?? $company->full_name ?? $company->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if($tab === 'create' && $managers->isNotEmpty())
                    <select name="manager_id" class="orders-page__select" data-orders-filter-auto-submit>
                        <option value="">Все менеджеры</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ (string)request('manager_id') === (string)$manager->id ? 'selected' : '' }}>
                                {{ $manager->full_name ?? $manager->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if(request()->except('tab'))
                    <a href="{{ route('admin.invoices.index', ['tab' => $tab]) }}" class="orders-page__btn orders-page__btn--outline">
                        <i class="fas fa-times"></i><span class="orders-page__btn-text">Сбросить</span>
                    </a>
                @endif

                <div class="orders-page__search-group">
                    <input type="text"
                           name="number"
                           value="{{ request('number') }}"
                           class="orders-page__search-input"
                           placeholder="{{ $tab === 'create' ? 'Номер заказа' : 'Номер счёта' }}">
                    <button type="submit" class="orders-page__search-submit" aria-label="Искать">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="orders-page__body">
            @if($tab === 'create')
                @if($orders->isNotEmpty())
                    <div class="orders-page__table-wrap">
                        <table class="orders-page__table">
                            <thead>
                                <tr>
                                    <th class="orders-page__th orders-page__th--number">№ Заказа</th>
                                    <th class="orders-page__th orders-page__th--status">Статус</th>
                                    <th class="orders-page__th orders-page__th--date">Дата, время</th>
                                    <th class="orders-page__th orders-page__th--author">Автор заказа</th>
                                    <th class="orders-page__th orders-page__th--company">Компания</th>
                                    <th class="orders-page__th orders-page__th--total">Сумма, руб.</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                                @php
                                    $badgeMod = $order->status === \App\Enums\OrderStatus::Completed ? 'completed' : ($order->status === \App\Enums\OrderStatus::Cancelled ? 'cancelled' : 'in-progress');
                                @endphp
                                <tr class="orders-page__row orders-page__row--clickable" data-href="{{ route('admin.orders.show', $order) }}">
                                    <td class="orders-page__td orders-page__td--number">
                                        <span class="orders-page__link">{{ $order->order_number }}</span>
                                    </td>
                                    <td class="orders-page__td orders-page__td--status">
                                        <span class="orders-page__badge orders-page__badge--{{ $badgeMod }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td class="orders-page__td orders-page__td--date">{{ $order->created_at->format('d.m.Y, H:i') }}</td>
                                    <td class="orders-page__td orders-page__td--author">
                                        {{ $order->user->full_name ?? $order->user->name }}
                                    </td>
                                    <td class="orders-page__td orders-page__td--company">
                                        @php
                                            $companyName = optional($order->companyAdmin)->company ?? optional($order->companyAdmin)->full_name ?? ($order->user->company ?? null);
                                        @endphp
                                        {{ $companyName ?? '—' }}
                                    </td>
                                    <td class="orders-page__td orders-page__td--total">{{ number_format($order->total, 2, '.', ' ') }} ₽</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="orders-page__empty">
                        <span class="orders-page__empty-icon"><i class="fas fa-clipboard-list"></i></span>
                        Нет заказов без счёта
                    </div>
                @endif
            @else
                @if($invoices->isNotEmpty())
                    <div class="orders-page__table-wrap">
                        <table class="orders-page__table">
                            <thead>
                                <tr>
                                    <th class="orders-page__th orders-page__th--number">Номер счета</th>
                                    <th class="orders-page__th orders-page__th--status">Статус</th>
                                    <th class="orders-page__th orders-page__th--date">Дата и время</th>
                                    <th class="orders-page__th orders-page__th--author">Менеджер/Компания</th>
                                    <th class="orders-page__th orders-page__th--total">Сумма, руб.</th>
                                    <th class="orders-page__th orders-page__th--actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($invoices as $invoice)
                                @php
                                    $truckStatus = $invoice->truck_status;
                                    $statusMod = $truckStatus->value === 'paid' ? 'completed' : ($truckStatus->value === 'cancelled' ? 'cancelled' : 'in-progress');
                                @endphp
                                <tr class="orders-page__row">
                                    <td class="orders-page__td orders-page__td--number">
                                        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="orders-page__link" target="_blank" rel="noopener noreferrer">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="orders-page__td orders-page__td--status">
                                        <span class="orders-page__badge orders-page__badge--{{ $statusMod }}">
                                            {{ $truckStatus->label() }}
                                        </span>
                                    </td>
                                    <td class="orders-page__td orders-page__td--date">{{ $invoice->created_at->format('d.m.Y, H:i') }}</td>
                                    <td class="orders-page__td orders-page__td--author">
                                        @php
                                            $order = $invoice->order;
                                            $managerName = $order ? ($order->user->full_name ?? $order->user->name) : null;
                                            $companyName = $order ? (optional($order->companyAdmin)->company ?? optional($order->companyAdmin)->full_name ?? $order->user->company ?? null) : null;
                                        @endphp
                                        {{ $managerName ?? '—' }} / {{ $companyName ?? '—' }}
                                    </td>
                                    <td class="orders-page__td orders-page__td--total">{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</td>
                                    <td class="orders-page__td orders-page__td--actions">
                                        <a href="{{ route('admin.invoices.pdf', $invoice) }}"
                                           class="orders-page__btn orders-page__btn--pdf"
                                           title="Скачать PDF"
                                           download>
                                            <span class="orders-page__btn-text">Скачать pdf</span>
                                            <i class="fas fa-download" aria-hidden="true"></i>
                                        </a>
                                        @if($invoice->order)
                                            <a href="{{ route('admin.orders.show', $invoice->order) }}"
                                               class="orders-page__btn orders-page__btn--check orders-page__btn--check-inline"
                                               title="К заказу">
                                                <span class="orders-page__btn-text">К заказу</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="orders-page__empty">
                        <span class="orders-page__empty-icon"><i class="fas fa-file-invoice"></i></span>
                        Счетов не найдено
                    </div>
                @endif
            @endif
        </div>

        @if($tab === 'create' && $orders->hasPages())
        <footer class="orders-page__footer">
            {{ $orders->withQueryString()->links('vendor.pagination.orders-page') }}
        </footer>
        @endif
        @if($tab !== 'create' && $invoices && $invoices->hasPages())
        <footer class="orders-page__footer">
            {{ $invoices->withQueryString()->links('vendor.pagination.orders-page') }}
        </footer>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var sel = document.querySelector('[data-invoices-tab-select]');
    if (sel) {
        sel.addEventListener('change', function() {
            var base = this.getAttribute('data-invoices-tab-base') || '';
            var params = new URLSearchParams(window.location.search);
            params.set('tab', this.value);
            window.location.href = base + (params.toString() ? '?' + params.toString() : '');
        });
    }
})();
(function() {
    document.querySelectorAll('[data-orders-filter-auto-submit]').forEach(function(select) {
        select.addEventListener('change', function() {
            if (this.form) this.form.submit();
        });
    });
})();
(function() {
    document.querySelectorAll('.orders-page__row--clickable[data-href]').forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a')) return;
            var href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
        row.style.cursor = 'pointer';
    });
})();
</script>
@endpush
