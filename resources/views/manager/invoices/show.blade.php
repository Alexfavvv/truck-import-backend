@extends('manager.layout')
@section('title', 'Счёт '.$invoice->invoice_number)
@section('content')
<div class="manager-card"><a href="{{ route('manager.invoices.index') }}">← К счетам</a><h1 class="mt-3">Счёт {{ $invoice->invoice_number }}</h1>
    <dl class="row"><dt class="col-sm-3">Компания</dt><dd class="col-sm-9">{{ $invoice->order->companyAdmin->company ?? '—' }}</dd><dt class="col-sm-3">Заказ</dt><dd class="col-sm-9"><a href="{{ route('manager.orders.show', $invoice->order) }}">{{ $invoice->order->order_number }}</a></dd><dt class="col-sm-3">Дата счёта</dt><dd class="col-sm-9">{{ $invoice->created_at->format('d.m.Y') }}</dd><dt class="col-sm-3">Сумма</dt><dd class="col-sm-9">{{ number_format($invoice->amount, 2, ',', ' ') }} ₽</dd><dt class="col-sm-3">Статус</dt><dd class="col-sm-9">{{ $invoice->truck_status->label() }}</dd></dl>
    <a class="btn btn-primary" href="{{ route('manager.invoices.pdf', $invoice) }}">Скачать PDF</a>
</div>
@endsection
