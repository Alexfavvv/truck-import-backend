@extends('manager.layout')

@section('title', 'Профиль компании')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="card-title">{{ $company->company ?? $company->full_name }}</h1>
            <a href="{{ route('manager.companies.index') }}">← К компаниям</a>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">ID заказчика</dt><dd class="col-sm-9">{{ $company->customer_id ?? '—' }}</dd>
                <dt class="col-sm-3">Контакт</dt><dd class="col-sm-9">{{ $company->full_name ?? '—' }}</dd>
                <dt class="col-sm-3">Телефон</dt><dd class="col-sm-9">{{ $company->phone ?? '—' }}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $company->email ?? '—' }}</dd>
                <dt class="col-sm-3">ИНН</dt><dd class="col-sm-9">{{ $company->inn ?? '—' }}</dd>
                <dt class="col-sm-3">ОГРН</dt><dd class="col-sm-9">{{ $company->ogrn ?? '—' }}</dd>
            </dl>
            <p class="text-muted mb-0">Профиль компании доступен только для просмотра.</p>
        </div>
    </div>
</div>
@endsection
