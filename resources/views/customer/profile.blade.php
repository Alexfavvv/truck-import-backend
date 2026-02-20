@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid"><h1 class="m-0">Профиль</h1></div>
</div>
<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        <div class="row">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Мои данные</h3></div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('customer.profile.update') }}">
                            @csrf @method('PUT')

                            <div class="form-group">
                                <label>ФИО</label>
                                <input type="text" name="full_name" class="form-control"
                                       value="{{ old('full_name', $user->full_name) }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Телефон <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control" required
                                               value="{{ old('phone', $user->phone) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control"
                                               value="{{ old('email', $user->email) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Компания</label>
                                <input type="text" name="company" class="form-control"
                                       value="{{ old('company', $user->company) }}">
                            </div>
                            <div class="form-group">
                                <label>Должность</label>
                                <input type="text" name="position" class="form-control"
                                       value="{{ old('position', $user->position) }}">
                            </div>
                            <div class="form-group">
                                <label>Реквизиты</label>
                                <textarea name="requisites" class="form-control" rows="3">{{ old('requisites', $user->requisites) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Сохранить
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Данные аккаунта</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr><th class="pl-3">Логин</th><td>{{ $user->login }}</td></tr>
                            @if($user->customer_id)
                            <tr><th class="pl-3">ID заказчика</th><td><code>{{ $user->customer_id }}</code></td></tr>
                            @endif
                            <tr><th class="pl-3">Роль</th><td>{{ $user->role?->label() }}</td></tr>
                            <tr><th class="pl-3">Зарегистрирован</th><td>{{ $user->created_at->format('d.m.Y') }}</td></tr>
                        </table>
                    </div>
                </div>

                @if($user->isAdminCustomer())
                <div class="card card-danger card-outline">
                    <div class="card-header"><h3 class="card-title text-danger">Удалить аккаунт</h3></div>
                    <div class="card-body">
                        <p class="text-sm text-muted">
                            При удалении аккаунта будут удалены все прикреплённые менеджеры и все заказы компании.
                            Действие необратимо.
                        </p>
                        <button class="btn btn-danger btn-block btn-sm"
                                data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash-alt mr-1"></i>Удалить мой аккаунт
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

@if(auth()->user()->isAdminCustomer())
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customer.profile.destroy') }}">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Удаление аккаунта
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-2">
                        <strong>Внимание!</strong> Будут удалены:
                        <ul class="mb-0 mt-1">
                            <li>Все прикреплённые менеджеры</li>
                            <li>Все заказы компании</li>
                        </ul>
                    </div>
                    <p class="text-danger mb-0"><strong>Это действие необратимо.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить аккаунт</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
