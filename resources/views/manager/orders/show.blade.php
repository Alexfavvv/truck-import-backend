@extends('manager.layout')
@section('title', 'Заказ '.$order->order_number)
@section('content')
<div class="manager-card"><a href="{{ route('manager.orders.index') }}">← К заказам</a><h1 class="mt-3">Заказ {{ $order->order_number }}</h1>
    <dl class="row"><dt class="col-sm-3">Компания</dt><dd class="col-sm-9">{{ $order->companyAdmin->company ?? '—' }}</dd><dt class="col-sm-3">Дата</dt><dd class="col-sm-9">{{ $order->created_at->format('d.m.Y H:i') }}</dd><dt class="col-sm-3">Статус</dt><dd class="col-sm-9">{{ $order->status->label() }}</dd></dl>
    <div class="table-responsive"><table class="table"><thead><tr><th>Артикул</th><th>Товар</th><th>Количество</th><th>Цена</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->product->sku ?? '—' }}</td><td>{{ $item->product->title ?? '—' }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->price, 2, ',', ' ') }} ₽</td></tr>@endforeach</tbody></table></div>
    <p><strong>Итого:</strong> {{ number_format($order->total, 2, ',', ' ') }} ₽</p>
    @if($order->notes)<p><strong>Примечание:</strong> {{ $order->notes }}</p>@endif
    @if($order->invoice)<a class="btn btn-outline-secondary" href="{{ route('manager.invoices.show', $order->invoice) }}">Открыть счёт</a>@endif
    <form method="POST" action="{{ route('manager.orders.status', $order) }}" class="manager-actions mt-4" data-submit-loading>@csrf @method('PATCH')<label for="status">Изменить статус</label><select id="status" name="status" class="form-control" style="max-width:220px">@foreach(\App\Enums\OrderStatus::changeStatusValues() as $value)<option value="{{ $value }}" {{ $order->status->value === $value ? 'selected' : '' }}>{{ \App\Enums\OrderStatus::tryFrom($value)?->label() ?? 'В работе' }}</option>@endforeach</select><button class="btn btn-primary">Сохранить</button></form>
</div>
@endsection
