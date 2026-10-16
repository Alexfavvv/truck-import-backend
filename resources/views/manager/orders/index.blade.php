@extends('manager.layout')
@section('title', 'Заказы')
@section('content')
<div class="manager-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap"><h1>Заказы компаний</h1><a class="btn btn-primary mb-3" href="{{ route('manager.orders.create') }}">Создать заказ</a></div>
    <form method="GET" class="manager-actions" role="search">
        <input class="form-control" style="max-width:220px" name="number" value="{{ request('number') }}" placeholder="Номер заказа" aria-label="Номер заказа">
        <select class="form-control" style="max-width:260px" name="company_id" aria-label="Компания"><option value="">Все компании</option>@foreach($companies as $company)<option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->company ?? $company->full_name }}</option>@endforeach</select>
        <select class="form-control" style="max-width:200px" name="status" aria-label="Статус"><option value="">Все статусы</option>@foreach(\App\Enums\OrderStatus::filterOptions() as $status)<option value="{{ $status['value'] }}" {{ request('status') === $status['value'] ? 'selected' : '' }}>{{ $status['label'] }}</option>@endforeach</select>
        <button class="btn btn-primary">Показать</button><a href="{{ route('manager.orders.index') }}">Сбросить</a>
    </form>
    @if($orders->isEmpty())<p class="manager-empty">Заказов по выбранным условиям нет.</p>
    @else<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Номер</th><th>Компания</th><th>Статус</th><th>Дата</th><th>Автор</th></tr></thead><tbody>
    @foreach($orders as $order)<tr><td><a href="{{ route('manager.orders.show', $order) }}">{{ $order->order_number }}</a></td><td>{{ $order->companyAdmin->company ?? '—' }}</td><td>{{ $order->status->label() }}</td><td>{{ $order->created_at->format('d.m.Y') }}</td><td>{{ $order->user->full_name ?? $order->user->name ?? '—' }}</td></tr>@endforeach
    </tbody></table></div>{{ $orders->withQueryString()->links() }}@endif
</div>
@endsection
