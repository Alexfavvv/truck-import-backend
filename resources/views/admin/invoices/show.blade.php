@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Счёт {{ $invoice->invoice_number }}</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-pdf mr-1"></i>Скачать PDF
                </a>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>К списку
                </a>
            </div>
        </div>
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

        <div class="row">
            <div class="col-md-8">
                {{-- Позиции заказа --}}
                @if($invoice->order)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Позиции заказа
                            <a href="{{ route('admin.orders.show', $invoice->order) }}" class="ml-2 text-sm">
                                {{ $invoice->order->order_number }}
                            </a>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th><th>Товар</th><th class="text-right">Кол-во</th>
                                    <th class="text-right">Цена</th><th class="text-right">Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($invoice->order->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product->title ?? '—' }}</td>
                                    <td class="text-right">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->price, 2, '.', ' ') }} ₽</td>
                                    <td class="text-right">{{ number_format($item->subtotal, 2, '.', ' ') }} ₽</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">К оплате по счёту:</th>
                                    <th class="text-right">{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif

                @if($invoice->notes)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Примечание</h3></div>
                    <div class="card-body">{{ $invoice->notes }}</div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                {{-- Информация о счёте --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Информация</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr>
                                <th class="pl-3">Номер</th>
                                <td>{{ $invoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Выставил</th>
                                <td>
                                    {{ $invoice->creator->full_name ?? $invoice->creator->name }}
                                    <small class="text-muted d-block">{{ $invoice->creator->role?->label() }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th class="pl-3">Сумма</th>
                                <td><strong>{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</strong></td>
                            </tr>
                            <tr>
                                <th class="pl-3">Создан</th>
                                <td>{{ $invoice->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            @if($invoice->due_date)
                            <tr>
                                <th class="pl-3">Срок оплаты</th>
                                <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                            </tr>
                            @endif
                            @if($invoice->paid_at)
                            <tr>
                                <th class="pl-3">Оплачен</th>
                                <td class="text-success">{{ $invoice->paid_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Статус Трак Импорт --}}
                @can('changeTruckStatus', $invoice)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Статус (Трак Импорт)</h3></div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge {{ $invoice->truck_status->badgeClass() }}">
                                {{ $invoice->truck_status->label() }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('admin.invoices.truck-status', $invoice) }}">
                            @csrf @method('PATCH')
                            <div class="input-group">
                                <select name="truck_status" class="form-control form-control-sm">
                                    @foreach(\App\Enums\TruckInvoiceStatus::cases() as $s)
                                        <option value="{{ $s->value }}"
                                            {{ $invoice->truck_status === $s ? 'selected' : '' }}>
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

                {{-- Статус Заказчика --}}
                @can('changeCustomerStatus', $invoice)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Статус (Заказчик)</h3></div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge {{ $invoice->customer_status->badgeClass() }}">
                                {{ $invoice->customer_status->label() }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('admin.invoices.customer-status', $invoice) }}">
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

                @can('delete', $invoice)
                <div class="card card-danger card-outline">
                    <div class="card-header"><h3 class="card-title text-danger">Опасная зона</h3></div>
                    <div class="card-body">
                        <button class="btn btn-danger btn-block btn-sm"
                                data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash-alt mr-1"></i>Удалить счёт
                        </button>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</section>

@can('delete', $invoice)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Удаление счёта</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Удалить счёт <strong>{{ $invoice->invoice_number }}</strong>?</p>
                    <p class="text-danger"><strong>Это действие необратимо.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
