@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Мои заказы</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('customer.orders.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i>Создать заказ
                </a>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>№ Заказа</th>
                            @if(auth()->user()->isAdminCustomer())<th>Создал</th>@endif
                            <th>Статус</th>
                            <th>Сумма</th>
                            <th>Дата</th>
                            <th>Счёт</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('customer.orders.show', $order) }}">
                                    <strong>{{ $order->order_number }}</strong>
                                </a>
                            </td>
                            @if(auth()->user()->isAdminCustomer())
                            <td>
                                {{ $order->user->full_name ?? $order->user->name }}
                                @if($order->user_id !== auth()->id())
                                    <small class="text-muted">(менеджер)</small>
                                @endif
                            </td>
                            @endif
                            <td>
                                <span class="badge {{ $order->status->badgeClass() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td>{{ number_format($order->total, 2, '.', ' ') }} ₽</td>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($order->invoice)
                                    <a href="{{ route('customer.invoices.pdf', $order->invoice) }}"
                                       class="btn btn-xs btn-success" title="Скачать PDF">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                @else
                                    <span class="text-muted" style="font-size:11px">Нет счёта</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                Заказов пока нет. <a href="{{ route('customer.orders.create') }}">Создать первый?</a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
