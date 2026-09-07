@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Редактировать: {{ $user->full_name ?? $user->name }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Назад
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Данные пользователя</h3></div>
                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label>Роль</label>
                                <input type="text" class="form-control" value="{{ $user->role?->label() }}" disabled>
                            </div>

                            <div class="form-group">
                                <label>ФИО</label>
                                <input type="text" name="full_name" class="form-control"
                                       value="{{ old('full_name', $user->full_name) }}">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Телефон</label>
                                        <input type="text" name="phone" class="form-control"
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

                            @if($user->isCustomerSide())
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
                            @endif

                            @if(auth()->user()->isAdminTruck())
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Логин</label>
                                        <input type="text" name="login" class="form-control"
                                               value="{{ old('login', $user->login) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Новый пароль</label>
                                        <input type="text" name="password" class="form-control"
                                               placeholder="Оставьте пустым, чтобы не менять">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="isActive"
                                           name="is_active" value="1"
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isActive">Активен</label>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Сохранить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
