@extends('manager.layout')

@section('title', 'Заказ '.$order->order_number)

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="card-title">Заказ {{ $order->order_number }}</h1>
            <a href="{{ route('manager.orders.index') }}">← К заказам</a>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Компания</dt><dd class="col-sm-9">{{ $order->companyAdmin->company ?? '—' }}</dd>
                <dt class="col-sm-3">Дата</dt><dd class="col-sm-9">{{ $order->created_at->format('d.m.Y H:i') }}</dd>
                <dt class="col-sm-3">Статус</dt><dd class="col-sm-9">{{ $order->status->label() }}</dd>
            </dl>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Артикул</th><th>Товар</th><th>Количество</th><th>Цена</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->sku ?? '—' }}</td>
                                <td>{{ $item->product->title ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2, ',', ' ') }} ₽</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="h5 mb-3"><strong>Итого:</strong> {{ number_format($order->total, 2, ',', ' ') }} ₽</p>
            @if($order->notes)<p><strong>Примечание:</strong> {{ $order->notes }}</p>@endif
            @if($order->invoice)
                <a class="btn btn-outline-secondary mb-3" href="{{ route('manager.invoices.show', $order->invoice) }}">Открыть счёт</a>
            @endif

            <form method="POST" action="{{ route('manager.orders.status', $order) }}" class="form-row align-items-end mt-3" data-submit-loading>
                @csrf
                @method('PATCH')
                <div class="form-group col-sm-5 col-md-4">
                    <label for="status">Изменить статус</label>
                    <select id="status" name="status" class="form-control">
                        @foreach(\App\Enums\OrderStatus::changeStatusValues() as $value)
                            <option value="{{ $value }}" {{ $order->status->value === $value ? 'selected' : '' }}>{{ \App\Enums\OrderStatus::tryFrom($value)?->label() ?? 'В работе' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-sm-auto"><button class="btn btn-primary">Сохранить</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
