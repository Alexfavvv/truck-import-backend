@extends('manager.layout')
@section('title', 'Профиль компании')
@section('content')
<div class="manager-card"><a href="{{ route('manager.companies.index') }}">← К компаниям</a><h1 class="mt-3">{{ $company->company ?? $company->full_name }}</h1>
    <dl class="row"><dt class="col-sm-3">ID заказчика</dt><dd class="col-sm-9">{{ $company->customer_id ?? '—' }}</dd><dt class="col-sm-3">Контакт</dt><dd class="col-sm-9">{{ $company->full_name ?? '—' }}</dd><dt class="col-sm-3">Телефон</dt><dd class="col-sm-9">{{ $company->phone ?? '—' }}</dd><dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $company->email ?? '—' }}</dd><dt class="col-sm-3">ИНН</dt><dd class="col-sm-9">{{ $company->inn ?? '—' }}</dd><dt class="col-sm-3">ОГРН</dt><dd class="col-sm-9">{{ $company->ogrn ?? '—' }}</dd></dl>
    <p class="text-muted">Профиль компании доступен только для просмотра.</p>
</div>
@endsection
