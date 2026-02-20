@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid"><h1 class="m-0">Счета на оплату</h1></div>
</div>
<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>№ Счёта</th>
                            <th>Заказ</th>
                            <th>Сумма</th>
                            <th>Статус ТИ</th>
                            <th>Мой статус</th>
                            <th>Срок</th>
                            <th style="width:110px">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('customer.invoices.show', $invoice) }}">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>
                                @if($invoice->order)
                                    <a href="{{ route('customer.orders.show', $invoice->order) }}">
                                        {{ $invoice->order->order_number }}
                                    </a>
                                @else —
                                @endif
                            </td>
                            <td><strong>{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</strong></td>
                            <td>
                                <span class="badge {{ $invoice->truck_status->badgeClass() }}">
                                    {{ $invoice->truck_status->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $invoice->customer_status->badgeClass() }}">
                                    {{ $invoice->customer_status->label() }}
                                </span>
                            </td>
                            <td>
                                @if($invoice->due_date)
                                    <span class="{{ $invoice->due_date->isPast() && $invoice->truck_status->value !== 'paid' ? 'text-danger font-weight-bold' : '' }}">
                                        {{ $invoice->due_date->format('d.m.Y') }}
                                    </span>
                                @else —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('customer.invoices.pdf', $invoice) }}"
                                   class="btn btn-sm btn-success" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="{{ route('customer.invoices.show', $invoice) }}"
                                   class="btn btn-sm btn-info ml-1" title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                Счетов пока нет
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-center">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
