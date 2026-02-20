@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Счёт {{ $invoice->invoice_number }}</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('customer.invoices.pdf', $invoice) }}" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-pdf mr-1"></i>Скачать PDF
                </a>
                <a href="{{ route('customer.invoices.index') }}" class="btn btn-secondary btn-sm">
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
                @if($invoice->order?->items->count())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Позиции</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>#</th><th>Товар</th><th class="text-right">Кол-во</th>
                                <th class="text-right">Цена</th><th class="text-right">Сумма</th></tr></thead>
                            <tbody>
                            @foreach($invoice->order->items as $i => $item)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $item->product->title ?? '—' }}</td>
                                    <td class="text-right">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->price, 2, '.', ' ') }} ₽</td>
                                    <td class="text-right">{{ number_format($item->subtotal, 2, '.', ' ') }} ₽</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">К оплате:</th>
                                    <th class="text-right">{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Информация</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr><th class="pl-3">Номер</th><td>{{ $invoice->invoice_number }}</td></tr>
                            <tr><th class="pl-3">Сумма</th><td><strong>{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</strong></td></tr>
                            <tr><th class="pl-3">Статус ТИ</th>
                                <td><span class="badge {{ $invoice->truck_status->badgeClass() }}">{{ $invoice->truck_status->label() }}</span></td></tr>
                            <tr><th class="pl-3">Мой статус</th>
                                <td><span class="badge {{ $invoice->customer_status->badgeClass() }}">{{ $invoice->customer_status->label() }}</span></td></tr>
                            @if($invoice->due_date)
                            <tr><th class="pl-3">Срок</th><td>{{ $invoice->due_date->format('d.m.Y') }}</td></tr>
                            @endif
                            <tr><th class="pl-3">Выставлен</th><td>{{ $invoice->created_at->format('d.m.Y') }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Сменить статус со своей стороны --}}
                @can('changeCustomerStatus', $invoice)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Подтвердить оплату</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('customer.invoices.status', $invoice) }}">
                            @csrf @method('PATCH')
                            <div class="input-group">
                                <select name="customer_status" class="form-control form-control-sm">
                                    @foreach(\App\Enums\CustomerInvoiceStatus::cases() as $s)
                                        <option value="{{ $s->value }}"
                                            {{ $invoice->customer_status === $s ? 'selected' : '' }}>
                                            {{ $s->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-sm" type="submit">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</section>
@endsection
