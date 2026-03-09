@extends('customer.layouts.app')

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
                <select class="orders-page__tabs-select" data-orders-tab-select data-orders-tab-base="{{ route('customer.orders.index') }}">
                    <option value="my" {{ $tab === 'my' ? 'selected' : '' }}>Мои заказы</option>
                    @if(auth()->user()->isAdminCustomer())
                    <option value="company" {{ $tab === 'company' ? 'selected' : '' }}>Заказы компании заказчика</option>
                    @endif
                </select>
            </div>
            <ul class="orders-page__tabs">
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'my' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('customer.orders.index', ['tab' => 'my']) }}">
                        Мои заказы
                    </a>
                </li>
                @if(auth()->user()->isAdminCustomer())
                <li class="orders-page__tab">
                    <a class="orders-page__tab-link {{ $tab === 'company' ? 'orders-page__tab-link--active' : '' }}"
                       href="{{ route('customer.orders.index', ['tab' => 'company']) }}">
                        Заказы компании
                    </a>
                </li>
                @endif
            </ul>
            @can('create', \App\Models\Order::class)
                <a href="{{ route('customer.orders.create') }}" class="orders-page__btn orders-page__btn--success orders-page__btn--create">
                    <i class="fas fa-plus"></i><span class="orders-page__btn-text">Создать заказ</span>
                </a>
            @endcan
        </div>

        <header class="orders-page__section-header">
            <h1 class="orders-page__title">
                @if($tab === 'company')
                    Заказы компании <span class="orders-page__title-company">{{ auth()->user()->company ?? auth()->user()->full_name ?? auth()->user()->name }}</span>
                @else
                    Мои заказы
                @endif
            </h1>
        </header>

        <div class="orders-page__filters-wrap">
            <form method="GET" class="orders-page__filters">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($tab === 'my' || $tab === 'company')
                    <select name="status" class="orders-page__select" data-orders-filter-auto-submit>
                        <option value="">Все статусы</option>
                        @foreach($statuses as $option)
                            <option value="{{ $option['value'] }}" {{ (string)request('status') === (string)$option['value'] ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                @endif

                

                @if($tab === 'company')
                    @if($managers->isNotEmpty())
                        <select name="manager_id" class="orders-page__select" data-orders-filter-auto-submit>
                            <option value="">Все менеджеры</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ (string)request('manager_id') === (string)$manager->id ? 'selected' : '' }}>
                                    {{ $manager->full_name ?? $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                @endif

                @if(request()->except('tab'))
                    <a href="{{ route('customer.orders.index', ['tab' => $tab]) }}" class="orders-page__btn orders-page__btn--outline">
                        <i class="fas fa-times"></i><span class="orders-page__btn-text">Сбросить</span>
                    </a>
                @endif
                <div class="orders-page__search-group">
                    <input type="text"
                           name="number"
                           value="{{ request('number') }}"
                           class="orders-page__search-input"
                           placeholder="Введите номер заказа">
                    <button type="submit" class="orders-page__search-submit" aria-label="Искать">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="orders-page__body">
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
                            <tr class="orders-page__row">
                                <td class="orders-page__td orders-page__td--number">
                                    <a href="{{ route('customer.orders.show', $order) }}" class="orders-page__link">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="orders-page__td orders-page__td--status">
                                    <span class="orders-page__badge orders-page__badge--{{ $badgeMod }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="orders-page__td orders-page__td--date">{{ $order->created_at->format('d.m.Y, H:i') }}</td>
                                <td class="orders-page__td orders-page__td--author">
                                    <span class="orders-page__author-name">{{ $order->user->full_name ?? $order->user->name }}</span>
                                    <!-- <span class="orders-page__author-role">{{ $order->user->role?->label() }}</span> -->
                                </td>
                                <td class="orders-page__td orders-page__td--company">
                                    {{ optional($order->companyAdmin)->company ?? optional($order->companyAdmin)->full_name ?? '—' }}
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
                    Заказов не найдено
                </div>
            @endif
        </div>
        @if($orders->hasPages())
        <footer class="orders-page__footer">
            {{ $orders->withQueryString()->links('vendor.pagination.orders-page') }}
        </footer>
        @endif
    </div>
</div>

{{-- Модальное окно подтверждения удаления --}}
<div class="orders-page__modal" id="deleteOrderModal" tabindex="-1" role="dialog" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="orders-page__modal-dialog" role="document">
        <form id="deleteOrderForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="orders-page__modal-content">
                <div class="orders-page__modal-header">
                    <h2 class="orders-page__modal-title" id="deleteOrderModalLabel">
                        <i class="fas fa-exclamation-triangle orders-page__modal-title-icon"></i>Удаление заказа
                    </h2>
                    <button type="button" class="orders-page__modal-close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="orders-page__modal-body">
                    <p class="orders-page__modal-text">Вы уверены, что хотите удалить этот заказ?</p>
                    <p class="orders-page__modal-warning">Это действие необратимо.</p>
                </div>
                <div class="orders-page__modal-footer">
                    <button type="button" class="orders-page__btn orders-page__btn--secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="orders-page__btn orders-page__btn--danger">Удалить</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var sel = document.querySelector('[data-orders-tab-select]');
    if (sel) {
        sel.addEventListener('change', function() {
            var base = this.getAttribute('data-orders-tab-base') || '';
            var params = new URLSearchParams(window.location.search);
            params.set('tab', this.value);
            window.location.href = base + (params.toString() ? '?' + params.toString() : '');
        });
    }
})();
(function() {
    document.querySelectorAll('[data-orders-filter-auto-submit]').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
})();
(function() {
    var modal = document.getElementById('deleteOrderModal');
    var form = document.getElementById('deleteOrderForm');
    if (!modal || !form) return;
    document.querySelectorAll('[data-orders-modal-open]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            form.action = this.getAttribute('data-action');
            modal.classList.add('orders-page__modal--open');
            modal.setAttribute('aria-hidden', 'false');
        });
    });
    function closeModal() {
        modal.classList.remove('orders-page__modal--open');
        modal.setAttribute('aria-hidden', 'true');
    }
    modal.querySelectorAll('[data-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
})();
</script>
@endpush
