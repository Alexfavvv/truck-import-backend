@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $manager->full_name ?? $manager->name }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('customer.managers.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>К списку
                </a>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center pt-4">
                        <i class="fas fa-user-circle fa-4x text-muted mb-3"></i>
                        <h4>{{ $manager->full_name ?? $manager->name }}</h4>
                        <span class="badge badge-secondary">Менеджер заказчика</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr><th class="pl-3">Телефон</th><td>{{ $manager->phone ?? '—' }}</td></tr>
                            <tr><th class="pl-3">Email</th><td>{{ $manager->email ?? '—' }}</td></tr>
                            <tr><th class="pl-3">Заказов</th><td>{{ $manager->orders->count() }}</td></tr>
                            <tr><th class="pl-3">Добавлен</th><td>{{ $manager->created_at->format('d.m.Y') }}</td></tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-danger btn-sm btn-block"
                                data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash-alt mr-1"></i>Удалить менеджера
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            История заказов ({{ $manager->orders->count() }})
                        </h3>
                    </div>
                    @if($manager->orders->count())
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Номер</th><th>Статус</th><th>Сумма</th><th>Дата</th></tr></thead>
                            <tbody>
                            @foreach($manager->orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>
                                        <span class="badge {{ $order->status->badgeClass() }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($order->total, 2, '.', ' ') }} ₽</td>
                                    <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-clipboard fa-2x mb-2 d-block"></i>Заказов нет
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customer.managers.destroy', $manager) }}">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Удалить менеджера</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Удалить <strong>{{ $manager->full_name ?? $manager->name }}</strong>?</p>
                    <p class="text-muted">Все заказы менеджера будут сохранены в архиве.</p>
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
