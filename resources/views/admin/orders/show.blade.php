@extends('admin.layouts.main')

@section('content')
<div class="orders-page">
    @if(session('success'))
        <div class="orders-page__alert orders-page__alert--success" role="alert">
            {{ session('success') }}
            <button type="button" class="orders-page__alert-close" data-dismiss="alert" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @php
        $badgeMod = $order->status === \App\Enums\OrderStatus::Completed
            ? 'completed'
            : ($order->status === \App\Enums\OrderStatus::Cancelled ? 'cancelled' : 'in-progress');
        $isInProgress = in_array(
            $order->status->value,
            \App\Enums\OrderStatus::inProgressValues(),
            true
        );
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
        $isAdminTruck = $authUser?->isAdminTruck() ?? false;
        $showOrderActions = $isAdminTruck && $isInProgress;
    @endphp

    <div class="orders-page__detail-layout">
        <div class="orders-page__detail-back">
            <a href="{{ route('admin.orders.index') }}" class="orders-page__back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Ко всем заказам</span>
            </a>
        </div>

        <div class="orders-page__detail-main">
            <div class="orders-page__detail-header">
                <div class="orders-page__detail-title-row">
                    <h1 class="orders-page__title">
                        Заказ {{ $order->order_number }} от {{ $order->created_at->format('d.m.Y') }}
                    </h1>
                    <span class="orders-page__badge orders-page__badge--{{ $badgeMod }}">
                        {{ $order->status->label() }}
                    </span>
                </div>
            </div>

            <div class="orders-page__detail-table">
                <div class="orders-page__table-wrap">
                    <table class="orders-page__table">
                        <thead>
                        <tr>
                            @if($showOrderActions)
                                <th class="orders-page__th orders-page__th--select">
                                    <label class="custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-checkbox__input"
                                               data-order-items-select-all>
                                        <span class="custom-checkbox__box" aria-hidden="true"></span>
                                    </label>
                                </th>
                            @endif
                            <th class="orders-page__th orders-page__th--sku">Артикул</th>
                            <th class="orders-page__th orders-page__th--title">Наименование</th>
                            <th class="orders-page__th orders-page__th--qty">Кол-во</th>
                            <th class="orders-page__th orders-page__th--total">Сумма, руб.</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($order->items as $item)
                            <tr class="orders-page__row">
                                @if($showOrderActions)
                                    <td class="orders-page__td orders-page__td--select">
                                        <label class="custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-checkbox__input"
                                                   data-order-item-checkbox
                                                   value="{{ $item->id }}">
                                            <span class="custom-checkbox__box" aria-hidden="true"></span>
                                        </label>
                                    </td>
                                @endif
                                <td class="orders-page__td orders-page__td--sku">
                                    <span class="orders-page__link">
                                        {{ $item->product->sku ?? '—' }}
                                    </span>
                                </td>
                                <td class="orders-page__td orders-page__td--title">
                                    {{ $item->product->title ?? '—' }}
                                </td>
                                <td class="orders-page__td orders-page__td--qty">
                                    {{ $item->quantity }}
                                </td>
                                <td class="orders-page__td orders-page__td--total">
                                    {{ number_format($item->subtotal, 2, '.', ' ') }} ₽
                                </td>
                            </tr>
                        @empty
                            <tr class="orders-page__row">
                                <td class="orders-page__td orders-page__td--empty" colspan="{{ $showOrderActions ? 5 : 4 }}">
                                    Позиции не добавлены
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr>
                            <th class="orders-page__th" colspan="{{ $showOrderActions ? 4 : 3 }}">Итого</th>
                            <th class="orders-page__th orders-page__th--total">
                                {{ number_format($order->total, 2, '.', ' ') }} ₽
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($order->notes)
                <div class="orders-page__detail-notes">
                    <h2 class="orders-page__detail-notes-title">Примечание к заказу</h2>
                    <div class="orders-page__detail-notes-body">
                        {{ $order->notes }}
                    </div>
                </div>
            @endif
        </div>

        <aside class="orders-page__detail-sidebar">
            <div class="orders-page__sidebar-card orders-page__sidebar-card--primary">
                <div class="orders-page__sidebar-row">
                    <span class="orders-page__sidebar-label">Общая стоимость</span>
                    <span class="orders-page__sidebar-value">
                        {{ number_format($order->total, 2, '.', ' ') }} ₽
                    </span>
                </div>
                <div class="orders-page__sidebar-separator"></div>
                <div class="orders-page__sidebar-row">
                    <span class="orders-page__sidebar-label">Дата оформления</span>
                    <span class="orders-page__sidebar-value">
                        {{ $order->created_at->format('d.m.Y') }}
                    </span>
                </div>
                @if($order->status !== \App\Enums\OrderStatus::Cancelled)
                    <div class="orders-page__sidebar-separator"></div>
                    @if($isInProgress)
                        @if($order->invoice)
                            <a href="{{ route('admin.invoices.pdf', $order->invoice) }}"
                               class="orders-page__btn orders-page__btn--check"
                               target="_blank"
                               rel="noopener noreferrer">
                                Смотреть счёт
                            </a>
                        @else
                            @can('create', \App\Models\Invoice::class)
                                <a href="{{ route('admin.invoices.create', ['order_id' => $order->id]) }}"
                                   class="orders-page__btn orders-page__btn--check">
                                    Выставить счёт
                                </a>
                            @else
                                <button type="button" class="orders-page__btn orders-page__btn--check" disabled>
                                    Смотреть счёт
                                </button>
                            @endcan
                        @endif
                        <button type="button" class="orders-page__btn orders-page__btn--reissue" disabled>
                            Перевыпустить счёт
                        </button>
                    @else
                        <button type="button" class="orders-page__btn orders-page__btn--secondary orders-page__btn--check" disabled>
                            Чеки по заказу
                        </button>
                        <button type="button" class="orders-page__btn orders-page__btn--secondary orders-page__btn--history" disabled>
                            История заказа
                        </button>
                    @endif
                @endif
            </div>

            <div class="orders-page__sidebar-card orders-page__sidebar-card--status">
                <div class="orders-page__sidebar-status-header">
                    <span class="orders-page__sidebar-status-label">Статус заказа</span>
                    @if($authUser && $authUser->canChangeOrderStatus())
                        <button type="button"
                                class="orders-page__btn orders-page__btn--history orders-page__btn--status"
                                data-toggle="modal"
                                data-target="#orderStatusModal"
                                aria-label="Изменить статус заказа">
                            <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                            Изменить
                        </button>
                    @endif
                </div>
                <div class="orders-page__sidebar-status-body">
                    <span class="orders-page__badge orders-page__badge--{{ $badgeMod }}">
                        {{ $order->status->label() }}
                    </span>
                </div>
            </div>

            <div class="orders-page__sidebar-card orders-page__sidebar-card--secondary">
                <div class="orders-page__sidebar-title">Автор заказа</div>
                <div class="orders-page__author-card">
                    <div class="orders-page__author-avatar">
                        @php
                            $authorName = $order->user->full_name ?? $order->user->name;
                            $initials = mb_substr($authorName, 0, 1, 'UTF-8');
                            $companyName =
                                optional($order->companyAdmin)->company
                                ?? optional($order->companyAdmin)->name
                                ?? ($order->user->company ?? null);
                        @endphp
                        <span>{{ $initials }}</span>
                    </div>
                        <div class="orders-page__author-info">
                            <div class="orders-page__author-name">
                                {{ $authorName }}
                            </div>
                            <div class="orders-page__author-id">
                                ID: {{ $order->user_id }}
                            </div>
                            @if($companyName)
                                <div class="orders-page__author-company">
                                    {{ $companyName }}
                                </div>
                            @endif
                        </div>
                </div>
            </div>
        </aside>
    </div>
</div>

@if($showOrderActions)
    <div class="orders-page__detail-actions" data-order-detail-actions>
        <div class="orders-page__detail-actions-container">
            <div class="orders-page__detail-actions-left">
                <button type="button"
                        class="orders-page__btn orders-page__btn--danger"
                        data-order-action="delete-items"
                        disabled>
                    Удалить выбранное
                </button>
            </div>
            <div class="orders-page__detail-actions-right">
                <button type="button"
                        class="orders-page__btn orders-page__btn--reissue"
                        data-order-action="cancel-order"
                        disabled>
                    Отменить заказ
                </button>
            </div>
        </div>
    </div>
@endif

@if($authUser && $authUser->canChangeOrderStatus())
<div class="modal fade" id="orderStatusModal" tabindex="-1" role="dialog" aria-labelledby="orderStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderStatusModalLabel">Изменить статус заказа</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <label for="order-status-select" class="orders-page__modal-label">Статус</label>
                    <select name="status" id="order-status-select" class="orders-page__select orders-page__select--modal" required>
                        @foreach($statusOptions as $opt)
                            @php
                                $current = $order->status;
                                $optIsInProgress = $opt['value'] === 'in_progress';
                                $currentIsInProgress = in_array($current->value, \App\Enums\OrderStatus::inProgressValues(), true);
                                $selected = ($optIsInProgress && $currentIsInProgress) || (!$optIsInProgress && $current->value === $opt['value']);
                            @endphp
                            <option value="{{ $opt['value'] }}" {{ $selected ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="orders-page__btn orders-page__btn--secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="orders-page__btn orders-page__btn--primary">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@can('delete', $order)
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Удаление заказа</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Удалить заказ <strong>{{ $order->order_number }}</strong>?</p>
                    <p class="text-danger"><strong>Это действие необратимо.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
