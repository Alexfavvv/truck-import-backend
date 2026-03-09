@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Заказ {{ $order->order_number }}</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('customer.orders.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>К списку
                </a>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Позиции заказа</h3>
                        <span class="badge {{ $order->status->badgeClass() }} badge-lg" style="font-size:0.9em">
                            {{ $order->status->label() }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>#</th><th>Товар</th><th class="text-right">Кол-во</th>
                                    <th class="text-right">Цена</th><th class="text-right">Сумма</th></tr>
                            </thead>
                            <tbody>
                            @forelse($order->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product->title ?? '—' }}</td>
                                    <td class="text-right">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->price, 2, '.', ' ') }} ₽</td>
                                    <td class="text-right">{{ number_format($item->subtotal, 2, '.', ' ') }} ₽</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Нет позиций</td></tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Итого:</th>
                                    <th class="text-right">{{ number_format($order->total, 2, '.', ' ') }} ₽</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Информация</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr><th class="pl-3">Номер</th><td>{{ $order->order_number }}</td></tr>
                            <tr><th class="pl-3">Создан</th><td>{{ $order->created_at->format('d.m.Y H:i') }}</td></tr>
                            <tr><th class="pl-3">Автор</th><td>{{ $order->user->full_name ?? $order->user->name }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Счёт --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Счёт на оплату</h3></div>
                    <div class="card-body">
                        @if($order->invoice)
                            <div class="mb-2">
                                <span class="badge {{ $order->invoice->truck_status->badgeClass() }}">
                                    {{ $order->invoice->truck_status->label() }}
                                </span>
                                <small class="d-block text-muted mt-1">{{ $order->invoice->invoice_number }}</small>
                                <strong>{{ number_format($order->invoice->amount, 2, '.', ' ') }} ₽</strong>
                            </div>
                            <a href="{{ route('customer.invoices.pdf', $order->invoice) }}"
                               class="btn btn-success btn-block btn-sm">
                                <i class="fas fa-file-pdf mr-1"></i>Скачать PDF
                            </a>
                            <a href="{{ route('customer.invoices.pdf', $order->invoice) }}"
                               class="btn btn-outline-secondary btn-block btn-sm mt-1"
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="fas fa-eye mr-1"></i>Смотреть счёт
                            </a>
                        @else
                            <p class="text-muted text-center mb-0">
                                <i class="fas fa-clock mr-1"></i>Счёт ещё не выставлен
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
