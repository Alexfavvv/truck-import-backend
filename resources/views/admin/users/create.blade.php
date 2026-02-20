@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Создать пользователя</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>К списку
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-7">
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
                                <label>Роль <span class="text-danger">*</span></label>
                                <select name="role" id="roleSelect"
                                        class="form-control @error('role') is-invalid @enderror" required>
                                    <option value="">— Выберите роль —</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->value }}"
                                            {{ old('role', $selectedRole) === $role->value ? 'selected' : '' }}>
                                            {{ $role->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            {{-- Привязка к admin_customer (для manager_customer) --}}
                            <div class="form-group" id="parentField" style="display:none">
                                <label>Администратор заказчика <span class="text-danger">*</span></label>
                                <select name="parent_id" class="form-control">
                                    <option value="">— Выберите —</option>
                                    @foreach($adminCustomers as $ac)
                                        <option value="{{ $ac->id }}"
                                            {{ old('parent_id', $parentId) == $ac->id ? 'selected' : '' }}>
                                            {{ $ac->full_name ?? $ac->name }}
                                            @if($ac->company)({{ $ac->company }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>ФИО <span class="text-danger">*</span></label>
                                <input type="text" name="full_name"
                                       class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name') }}" required>
                                @error('full_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Телефон <span class="text-danger">*</span></label>
                                        <input type="text" name="phone"
                                               class="form-control @error('phone') is-invalid @enderror"
                                               value="{{ old('phone') }}" required>
                                        @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}">
                                        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Поля для заказчиков --}}
                            <div id="customerFields">
                                <div class="form-group">
                                    <label>Компания</label>
                                    <input type="text" name="company" class="form-control"
                                           value="{{ old('company') }}">
                                </div>
                                <div class="form-group">
                                    <label>Должность</label>
                                    <input type="text" name="position" class="form-control"
                                           value="{{ old('position') }}">
                                </div>
                                <div class="form-group">
                                    <label>Реквизиты</label>
                                    <textarea name="requisites" class="form-control" rows="3">{{ old('requisites') }}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Логин</label>
                                        <input type="text" name="login"
                                               class="form-control @error('login') is-invalid @enderror"
                                               value="{{ old('login') }}"
                                               placeholder="Оставьте пустым — сгенерируется автоматически">
                                        @error('login')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Пароль</label>
                                        <input type="text" name="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               value="{{ old('password') }}"
                                               placeholder="Оставьте пустым — сгенерируется автоматически">
                                        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Создать пользователя
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card card-info card-outline">
                        <div class="card-header"><h3 class="card-title">Матрица прав</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Роль</th><th>Видит заказы</th><th>Счета</th></tr></thead>
                                <tbody>
                                    <tr><td><span class="badge badge-danger">Админ ТИ</span></td><td>Все</td><td>Создаёт/удаляет</td></tr>
                                    <tr><td><span class="badge badge-warning">Менеджер ТИ</span></td><td>Все</td><td>Просмотр</td></tr>
                                    <tr><td><span class="badge badge-primary">Админ Заказчика</span></td><td>Компании</td><td>Просмотр/оплата</td></tr>
                                    <tr><td><span class="badge badge-secondary">Менеджер Заказчика</span></td><td>Свои</td><td>Просмотр/оплата</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const roleSelect = document.getElementById('roleSelect');
    const parentField = document.getElementById('parentField');
    const customerFields = document.getElementById('customerFields');

    function updateFields() {
        const role = roleSelect.value;
        parentField.style.display = role === 'manager_customer' ? 'block' : 'none';
        customerFields.style.display = ['admin_customer', 'manager_customer'].includes(role) ? 'block' : 'none';
    }

    roleSelect.addEventListener('change', updateFields);
    updateFields();
</script>
@endpush
