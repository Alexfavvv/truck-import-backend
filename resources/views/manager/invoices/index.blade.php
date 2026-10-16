@extends('manager.layout')

@section('title', 'Счета на оплату')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header"><h1 class="card-title">Счета на оплату</h1></div>
        <div class="card-body">
            <form method="GET" class="form-row align-items-end mb-4" role="search">
                <div class="form-group col-md-4">
                    <label for="invoice-number">Номер счёта</label>
                    <input id="invoice-number" class="form-control" name="number" value="{{ request('number') }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="invoice-company">Компания</label>
                    <select id="invoice-company" class="form-control" name="company_id">
                        <option value="">Все компании</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->company ?? $company->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 d-flex flex-wrap align-items-center mb-0">
                    <button class="btn btn-primary mr-3">Показать</button>
                    <a href="{{ route('manager.invoices.index') }}">Сбросить</a>
                </div>
            </form>

            @if($invoices->isEmpty())
                <div class="alert alert-light text-center mb-0">Счетов по выбранным условиям нет.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead><tr><th>Номер</th><th>Заказ</th><th>Компания</th><th>Сумма</th><th>Статус</th><th>Документ</th></tr></thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td><a href="{{ route('manager.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                    <td><a href="{{ route('manager.orders.show', $invoice->order) }}">{{ $invoice->order->order_number }}</a></td>
                                    <td>{{ $invoice->order->companyAdmin->company ?? '—' }}</td>
                                    <td>{{ number_format($invoice->amount, 2, ',', ' ') }} ₽</td>
                                    <td>{{ $invoice->truck_status->label() }}</td>
                                    <td><a href="{{ route('manager.invoices.pdf', $invoice) }}">Скачать PDF</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $invoices->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
