@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Добавить менеджера</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('customer.managers.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Назад
                </a>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Данные менеджера</h3></div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('customer.managers.store') }}">
                            @csrf
                            <div class="form-group">
                                <label>ФИО <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name') }}" required>
                                @error('full_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Телефон <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required>
                                @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}">
                                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Логин и пароль будут сгенерированы автоматически и показаны после создания.
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus mr-1"></i>Создать менеджера
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
