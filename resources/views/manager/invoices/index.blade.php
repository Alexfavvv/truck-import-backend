@extends('manager.layout')
@section('title', 'Счета')
@section('content')
<div class="manager-card"><h1>Счета на оплату</h1>
    <form method="GET" class="manager-actions" role="search"><input class="form-control" style="max-width:230px" name="number" value="{{ request('number') }}" placeholder="Номер счёта" aria-label="Номер счёта"><select class="form-control" style="max-width:280px" name="company_id" aria-label="Компания"><option value="">Все компании</option>@foreach($companies as $company)<option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->company ?? $company->full_name }}</option>@endforeach</select><button class="btn btn-primary">Показать</button><a href="{{ route('manager.invoices.index') }}">Сбросить</a></form>
    @if($invoices->isEmpty())<p class="manager-empty">Счетов по выбранным условиям нет.</p>
    @else<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Номер</th><th>Заказ</th><th>Компания</th><th>Сумма</th><th>Статус</th><th></th></tr></thead><tbody>
    @foreach($invoices as $invoice)<tr><td><a href="{{ route('manager.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td><td><a href="{{ route('manager.orders.show', $invoice->order) }}">{{ $invoice->order->order_number }}</a></td><td>{{ $invoice->order->companyAdmin->company ?? '—' }}</td><td>{{ number_format($invoice->amount, 2, ',', ' ') }} ₽</td><td>{{ $invoice->truck_status->label() }}</td><td><a href="{{ route('manager.invoices.pdf', $invoice) }}">PDF</a></td></tr>@endforeach
    </tbody></table></div>{{ $invoices->withQueryString()->links() }}@endif
</div>
@endsection
