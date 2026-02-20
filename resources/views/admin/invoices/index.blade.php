@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Счета на оплату</h1></div>
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

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Список счетов</h3>
                @can('create', \App\Models\Invoice::class)
                    <a href="{{ route('admin.invoices.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i>Создать счёт
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>№ Счёта</th>
                                <th>Заказ</th>
                                <th>Создал</th>
                                <th>Сумма</th>
                                <th>Статус (ТИ)</th>
                                <th>Статус (Заказчик)</th>
                                <th>Срок оплаты</th>
                                <th style="width:110px">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice) }}">
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @if($invoice->order)
                                        <a href="{{ route('admin.orders.show', $invoice->order) }}">
                                            {{ $invoice->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $invoice->creator->full_name ?? $invoice->creator->name }}
                                    <small class="text-muted d-block">{{ $invoice->creator->role?->label() }}</small>
                                </td>
                                <td>{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</td>
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
                                        {{ $invoice->due_date->format('d.m.Y') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice) }}"
                                       class="btn btn-sm btn-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.invoices.pdf', $invoice) }}"
                                       class="btn btn-sm btn-success ml-1" title="Скачать PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @can('delete', $invoice)
                                        <button class="btn btn-sm btn-danger ml-1"
                                                data-toggle="modal" data-target="#deleteModal"
                                                data-action="{{ route('admin.invoices.destroy', $invoice) }}"
                                                onclick="setDeleteAction(this)" title="Удалить">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                    Счетов не найдено
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-center">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Удаление счёта</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить этот счёт?</p>
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
@endsection

@push('scripts')
<script>
    function setDeleteAction(btn) {
        document.getElementById('deleteForm').action = btn.getAttribute('data-action');
    }
</script>
@endpush
