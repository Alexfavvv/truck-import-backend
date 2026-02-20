@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">
            Добро пожаловать,
            {{ auth()->user()->full_name ?? auth()->user()->company ?? auth()->user()->name }}!
        </h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- Карточки статистики --}}
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-clipboard-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Всего заказов</span>
                        <span class="info-box-number">{{ $totalOrders }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ожидают оплаты</span>
                        <span class="info-box-number">{{ $pendingInvoices }}</span>
                    </div>
                </div>
            </div>
            @if(auth()->user()->isAdminCustomer())
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Менеджеров</span>
                        <span class="info-box-number">{{ auth()->user()->managers()->count() }}</span>
                    </div>
                </div>
            </div>
            @endif
            @if(auth()->user()->customer_id)
            <div class="col-md-3 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-id-card"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">ID заказчика</span>
                        <span class="info-box-number" style="font-size:14px">{{ auth()->user()->customer_id }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="row">
            {{-- Последние заказы --}}
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Последние заказы</h3>
                        <a href="{{ route('customer.orders.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i>Новый заказ
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Номер</th><th>Статус</th><th>Дата</th></tr></thead>
                            <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('customer.orders.show', $order) }}">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge {{ $order->status->badgeClass() }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Заказов пока нет</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                            Все заказы →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Счета --}}
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Последние счета</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Счёт</th><th>Сумма</th><th>Статус</th></tr></thead>
                            <tbody>
                            @forelse($recentInvoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('customer.invoices.show', $invoice) }}">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($invoice->amount, 0, '.', ' ') }} ₽</td>
                                    <td>
                                        <span class="badge {{ $invoice->truck_status->badgeClass() }}" style="font-size:10px">
                                            {{ $invoice->truck_status->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Счетов нет</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('customer.invoices.index') }}" class="btn btn-sm btn-outline-secondary">
                            Все счета →
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
