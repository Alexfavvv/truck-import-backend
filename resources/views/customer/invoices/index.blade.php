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
            {{-- Одна вкладка «Счета» — без табов, только заголовок --}}
        </div>

        <header class="orders-page__section-header">
            <h1 class="orders-page__title">Счета на оплату</h1>
        </header>

        <div class="orders-page__filters-wrap">
            <form method="GET" class="orders-page__filters">
                @if(isset($statusOptions) && count($statusOptions) > 0)
                    <select name="status" class="orders-page__select" data-orders-filter-auto-submit>
                        <option value="">Все статусы</option>
                        @foreach($statusOptions as $option)
                            <option value="{{ $option['value'] }}" {{ (string)request('status') === (string)$option['value'] ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @if(request()->hasAny(['status', 'number']))
                    <a href="{{ route('customer.invoices.index') }}" class="orders-page__btn orders-page__btn--outline">
                        <i class="fas fa-times"></i><span class="orders-page__btn-text">Сбросить</span>
                    </a>
                @endif
                <div class="orders-page__search-group">
                    <input type="text"
                           name="number"
                           value="{{ request('number') }}"
                           class="orders-page__search-input"
                           placeholder="Номер счёта">
                    <button type="submit" class="orders-page__search-submit" aria-label="Искать">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="orders-page__body">
            @if($invoices->isNotEmpty())
                <div class="orders-page__table-wrap">
                    <table class="orders-page__table">
                        <thead>
                            <tr>
                                <th class="orders-page__th orders-page__th--number">Номер счета</th>
                                <th class="orders-page__th orders-page__th--number">Заказ</th>
                                <th class="orders-page__th orders-page__th--status">Статус</th>
                                <th class="orders-page__th orders-page__th--date">Срок</th>
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
                                    <a href="{{ route('customer.invoices.pdf', $invoice) }}" class="orders-page__link" target="_blank" rel="noopener noreferrer">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="orders-page__td orders-page__td--number">
                                    @if($invoice->order)
                                        <a href="{{ route('customer.orders.show', $invoice->order) }}" class="orders-page__link">{{ $invoice->order->order_number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="orders-page__td orders-page__td--status">
                                    <span class="orders-page__badge orders-page__badge--{{ $statusMod }}">
                                        {{ $truckStatus->label() }}
                                    </span>
                                </td>
                                <td class="orders-page__td orders-page__td--date">
                                    @if($invoice->due_date)
                                        {{ $invoice->due_date->format('d.m.Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="orders-page__td orders-page__td--total">{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</td>
                                <td class="orders-page__td orders-page__td--actions">
                                    <a href="{{ route('customer.invoices.pdf', $invoice) }}"
                                       class="orders-page__btn orders-page__btn--pdf"
                                       title="Скачать PDF"
                                       download>
                                        <span class="orders-page__btn-text">Скачать pdf</span>
                                        <i class="fas fa-download" aria-hidden="true"></i>
                                    </a>
                                    @if($invoice->order)
                                        <a href="{{ route('customer.orders.show', $invoice->order) }}"
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
                    Счетов пока нет
                </div>
            @endif
        </div>

        @if($invoices->hasPages())
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
    document.querySelectorAll('[data-orders-filter-auto-submit]').forEach(function(select) {
        select.addEventListener('change', function() {
            if (this.form) this.form.submit();
        });
    });
})();
</script>
@endpush
