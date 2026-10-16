@extends('manager.layout')

@section('title', 'Заказы')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="card-title">Заказы компаний</h1>
            <a class="btn btn-primary" href="{{ route('manager.orders.create') }}">Создать заказ</a>
        </div>
        <div class="card-body">
            <form method="GET" class="form-row align-items-end mb-4" role="search">
                <div class="form-group col-md-4">
                    <label for="order-number">Номер заказа</label>
                    <input id="order-number" class="form-control" name="number" value="{{ request('number') }}" aria-label="Номер заказа">
                </div>
                <div class="form-group col-md-4">
                    <label for="order-company">Компания</label>
                    <select id="order-company" class="form-control" name="company_id">
                        <option value="">Все компании</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->company ?? $company->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="order-status">Статус</label>
                    <select id="order-status" class="form-control" name="status">
                        <option value="">Все статусы</option>
                        @foreach(\App\Enums\OrderStatus::filterOptions() as $status)
                            <option value="{{ $status['value'] }}" {{ request('status') === $status['value'] ? 'selected' : '' }}>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 d-flex flex-wrap align-items-center mb-0">
                    <button class="btn btn-primary mr-3">Показать</button>
                    <a href="{{ route('manager.orders.index') }}">Сбросить</a>
                </div>
            </form>

            @if($orders->isEmpty())
                <div class="alert alert-light text-center mb-0">Заказов по выбранным условиям нет.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead><tr><th>Номер</th><th>Компания</th><th>Статус</th><th>Дата</th><th>Автор</th></tr></thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td><a href="{{ route('manager.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->companyAdmin->company ?? '—' }}</td>
                                    <td>{{ $order->status->label() }}</td>
                                    <td>{{ $order->created_at->format('d.m.Y') }}</td>
                                    <td>{{ $order->user->full_name ?? $order->user->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
