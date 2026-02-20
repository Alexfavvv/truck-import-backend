@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Заказ {{ $order->order_number }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
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
            {{-- Основная информация --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Позиции заказа</h3>
                        <span class="badge {{ $order->status->badgeClass() }} badge-lg" style="font-size:0.95em">
                            {{ $order->status->label() }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Товар</th>
                                        <th>Артикул</th>
                                        <th class="text-right">Кол-во</th>
                                        <th class="text-right">Цена</th>
                                        <th class="text-right">Сумма</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($order->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->product->title ?? '—' }}</td>
                                        <td><small class="text-muted">{{ $item->product->article ?? '—' }}</small></td>
                                        <td class="text-right">{{ $item->quantity }}</td>
                                        <td class="text-right">{{ number_format($item->price, 2, '.', ' ') }} ₽</td>
                                        <td class="text-right">{{ number_format($item->subtotal, 2, '.', ' ') }} ₽</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Позиции не добавлены</td></tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Итого:</th>
                                        <th class="text-right">{{ number_format($order->total, 2, '.', ' ') }} ₽</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                @if($order->notes)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Примечание</h3></div>
                    <div class="card-body">{{ $order->notes }}</div>
                </div>
                @endif
            </div>

            {{-- Боковая панель --}}
            <div class="col-md-4">

                {{-- Данные заказа --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Информация</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr>
                                <th class="pl-3">Номер</th>
                                <td>{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Создан</th>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Автор</th>
                                <td>
                                    {{ $order->user->full_name ?? $order->user->name }}
                                    <small class="text-muted d-block">{{ $order->user->role?->label() }}</small>
                                </td>
                            </tr>
                            @if($order->companyAdmin)
                            <tr>
                                <th class="pl-3">Компания</th>
                                <td>{{ $order->companyAdmin->company ?? $order->companyAdmin->name }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Смена статуса (только Трак Импорт) --}}
                @can('updateStatus', $order)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Изменить статус</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                            @csrf @method('PATCH')
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->value }}"
                                            {{ $order->status === $status ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan

                {{-- Счёт --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Счёт на оплату</h3>
                    </div>
                    <div class="card-body">
                        @if($order->invoice)
                            <div class="mb-2">
                                <span class="badge {{ $order->invoice->truck_status->badgeClass() }}">
                                    {{ $order->invoice->truck_status->label() }}
                                </span>
                                <small class="text-muted ml-1">{{ $order->invoice->invoice_number }}</small>
                            </div>
                            <a href="{{ route('admin.invoices.pdf', $order->invoice) }}"
                               class="btn btn-success btn-block btn-sm">
                                <i class="fas fa-file-pdf mr-1"></i>Скачать счёт PDF
                            </a>
                            <a href="{{ route('admin.invoices.show', $order->invoice) }}"
                               class="btn btn-outline-secondary btn-block btn-sm mt-1">
                                <i class="fas fa-eye mr-1"></i>Просмотр счёта
                            </a>
                        @else
                            <p class="text-muted text-center mb-2">Счёт не выставлен</p>
                            @can('create', \App\Models\Invoice::class)
                                <a href="{{ route('admin.invoices.create', ['order_id' => $order->id]) }}"
                                   class="btn btn-primary btn-block btn-sm">
                                    <i class="fas fa-plus mr-1"></i>Создать счёт
                                </a>
                            @endcan
                        @endif
                    </div>
                </div>

                {{-- Удаление --}}
                @can('delete', $order)
                <div class="card card-danger card-outline">
                    <div class="card-header"><h3 class="card-title text-danger">Опасная зона</h3></div>
                    <div class="card-body">
                        <button class="btn btn-danger btn-block btn-sm"
                                data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash-alt mr-1"></i>Удалить заказ
                        </button>
                    </div>
                </div>
                @endcan

            </div>
        </div>
    </div>
</section>

@can('delete', $order)
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Удаление заказа</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Удалить заказ <strong>{{ $order->order_number }}</strong>?</p>
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
