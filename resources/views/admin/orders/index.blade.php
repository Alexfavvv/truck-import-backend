@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Заказы</h1>
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

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Список заказов</h3>
                @can('create', \App\Models\Order::class)
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i>Создать заказ
                    </a>
                @endcan
            </div>

            {{-- Фильтр по статусу --}}
            <div class="card-body pb-0">
                <form method="GET" class="form-inline flex-wrap">
                    <select name="status" class="form-control form-control-sm mr-2 mb-2">
                        <option value="">Все статусы</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-secondary btn-sm mr-2 mb-2" type="submit">
                        <i class="fas fa-filter mr-1"></i>Фильтр
                    </button>
                    @if(request('status'))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fas fa-times mr-1"></i>Сбросить
                        </a>
                    @endif
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>№ Заказа</th>
                                <th>Статус</th>
                                <th>Создал</th>
                                <th>Дата создания</th>
                                <th>Сумма</th>
                                <th style="width:120px">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}">
                                        <strong>{{ $order->order_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge {{ $order->status->badgeClass() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    {{ $order->user->full_name ?? $order->user->name }}
                                    <small class="text-muted d-block">{{ $order->user->role?->label() }}</small>
                                </td>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ number_format($order->total, 2, '.', ' ') }} ₽</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="btn btn-sm btn-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('delete', $order)
                                        <button class="btn btn-sm btn-danger ml-1"
                                                data-toggle="modal" data-target="#deleteModal"
                                                data-action="{{ route('admin.orders.destroy', $order) }}"
                                                onclick="setDeleteAction(this)" title="Удалить">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                    Заказов не найдено
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-center">
                {{ $orders->withQueryString()->links() }}
            </div>
        </div>
    </div>
</section>

{{-- Модальное окно подтверждения удаления --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Удаление заказа</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить этот заказ?</p>
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
