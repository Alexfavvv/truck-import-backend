@extends('admin.layouts.main')

@section('content')
@php $editing = isset($manager); @endphp
<section class="content p-3">
    <a href="{{ route('admin.truck-managers.index') }}" class="d-inline-block mb-3">← К менеджерам</a>
    <h1>{{ $editing ? 'Менеджер: '.$manager->full_name : 'Новый менеджер' }}</h1>
    @if(session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ $editing ? route('admin.truck-managers.update', $manager) : route('admin.truck-managers.store') }}" class="card" data-submit-loading>
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="form-group"><label for="full_name">ФИО</label><input id="full_name" name="full_name" type="text" class="form-control" maxlength="255" required value="{{ old('full_name', $manager->full_name ?? '') }}"></div>
            <div class="form-group"><label for="login">Уникальный логин</label><input id="login" name="login" type="text" class="form-control" maxlength="100" autocomplete="username" required value="{{ old('login', $manager->login ?? '') }}"></div>
            <div class="form-group"><label for="password">{{ $editing ? 'Новый пароль (оставьте пустым, чтобы сохранить текущий)' : 'Пароль' }}</label><input id="password" name="password" type="password" class="form-control" minlength="8" autocomplete="new-password" {{ $editing ? '' : 'required' }}></div>
            <div class="form-group"><label for="password_confirmation">Подтверждение пароля</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" {{ $editing ? '' : 'required' }}></div>
            @if($editing)
                <div class="form-group"><label for="is_active">Учётная запись</label><select id="is_active" name="is_active" class="form-control"><option value="1" {{ old('is_active', $manager->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Активна</option><option value="0" {{ old('is_active', $manager->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Отключена</option></select><small class="form-text text-muted">Отключение сохраняет историю и заказы менеджера.</small></div>
            @endif
            <fieldset><legend class="h5">Закреплённые компании</legend>
                @php $selected = old('company_ids', $editing ? $manager->assignedCompanies->pluck('id')->all() : []); @endphp
                @forelse($companies as $company)
                    <label class="d-block mb-2"><input type="checkbox" name="company_ids[]" value="{{ $company->id }}" {{ in_array($company->id, $selected) ? 'checked' : '' }}> {{ $company->company ?? $company->full_name }} @if($company->customer_id) ({{ $company->customer_id }}) @endif</label>
                @empty
                    <p class="text-muted">Компаний пока нет. Создайте заказчика, чтобы назначить его менеджеру.</p>
                @endforelse
            </fieldset>
        </div>
        <div class="card-footer"><button class="btn btn-primary" type="submit" data-submit-button>Сохранить</button></div>
    </form>
</section>
@endsection
@push('scripts')<script>document.querySelector('[data-submit-loading]')?.addEventListener('submit', function () { const button = this.querySelector('[data-submit-button]'); button.disabled = true; button.textContent = 'Сохранение…'; });</script>@endpush
