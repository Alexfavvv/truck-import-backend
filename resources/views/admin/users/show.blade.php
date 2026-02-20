@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $user->full_name ?? $user->name }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                @can('update', $user)
                    @if(auth()->user()->isAdminTruck() || (auth()->user()->isAdminCustomer() && $user->parent_id === auth()->id()))
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm mr-1">
                        <i class="fas fa-edit mr-1"></i>Редактировать
                    </a>
                    @endif
                @endcan
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
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
            <div class="col-md-4">
                {{-- Профиль --}}
                <div class="card card-primary card-outline">
                    <div class="card-body text-center pt-4">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-4x text-muted"></i>
                        </div>
                        <h4>{{ $user->full_name ?? $user->name }}</h4>
                        <span class="badge badge-primary mb-2">{{ $user->role?->label() ?? '—' }}</span>
                        @if(!$user->is_active)
                            <span class="badge badge-danger ml-1">Деактивирован</span>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            @if($user->customer_id)
                            <tr>
                                <th class="pl-3">ID заказчика</th>
                                <td><code>{{ $user->customer_id }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <th class="pl-3">Логин</th>
                                <td>{{ $user->login ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Email</th>
                                <td>{{ $user->email ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Телефон</th>
                                <td>{{ $user->phone ?? '—' }}</td>
                            </tr>
                            @if($user->company)
                            <tr>
                                <th class="pl-3">Компания</th>
                                <td>{{ $user->company }}</td>
                            </tr>
                            @endif
                            @if($user->position)
                            <tr>
                                <th class="pl-3">Должность</th>
                                <td>{{ $user->position }}</td>
                            </tr>
                            @endif
                            @if($user->parent)
                            <tr>
                                <th class="pl-3">Привязан к</th>
                                <td>
                                    <a href="{{ route('admin.users.show', $user->parent) }}">
                                        {{ $user->parent->full_name ?? $user->parent->name }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th class="pl-3">Зарегистрирован</th>
                                <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    @can('delete', $user)
                    <div class="card-footer">
                        <button class="btn btn-danger btn-sm btn-block"
                                data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash-alt mr-1"></i>Удалить пользователя
                        </button>
                    </div>
                    @endcan
                </div>

                {{-- Реквизиты --}}
                @if($user->requisites)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Реквизиты</h3></div>
                    <div class="card-body">
                        <p class="mb-0" style="white-space: pre-wrap">{{ $user->requisites }}</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-8">
                {{-- Менеджеры (для admin_customer) --}}
                @if($user->managers->count() > 0)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Менеджеры ({{ $user->managers->count() }})</h3>
                        @can('create', \App\Models\User::class)
                        <a href="{{ route('admin.users.create', ['role' => 'manager_customer', 'parent_id' => $user->id]) }}"
                           class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i>Добавить менеджера
                        </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Имя</th><th>Телефон</th><th>Заказов</th><th></th></tr>
                            </thead>
                            <tbody>
                            @foreach($user->managers as $manager)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.users.show', $manager) }}">
                                            {{ $manager->full_name ?? $manager->name }}
                                        </a>
                                    </td>
                                    <td>{{ $manager->phone ?? '—' }}</td>
                                    <td>{{ $manager->orders->count() }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $manager) }}"
                                           class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Заказы пользователя --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Заказы ({{ $user->orders->count() }})
                        </h3>
                    </div>
                    @if($user->orders->count() > 0)
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Номер</th><th>Статус</th><th>Сумма</th><th>Дата</th></tr>
                            </thead>
                            <tbody>
                            @foreach($user->orders->take(20) as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
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
                        <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                        Заказов нет
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@can('delete', $user)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Удаление пользователя
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Удалить пользователя <strong>{{ $user->full_name ?? $user->name }}</strong>?</p>
                    @if($user->isAdminCustomer())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <strong>Внимание!</strong> Будут удалены все прикреплённые менеджеры и все заказы компании.
                    </div>
                    @endif
                    <p class="text-danger mb-0"><strong>Это действие необратимо.</strong></p>
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
