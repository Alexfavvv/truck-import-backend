@extends('manager.layout')
@section('title', 'Мой профиль')
@section('content')
<div class="manager-card"><h1>Мой профиль</h1><p><strong>Логин:</strong> {{ $user->login }}</p>
    <form method="POST" action="{{ route('manager.profile.update') }}" data-submit-loading>@csrf @method('PUT')
        <div class="manager-form-grid"><div class="form-group"><label for="full_name">ФИО</label><input class="form-control" id="full_name" name="full_name" maxlength="255" required value="{{ old('full_name', $user->full_name) }}"></div><div class="form-group"><label for="phone">Телефон</label><input class="form-control" id="phone" name="phone" maxlength="30" value="{{ old('phone', $user->phone) }}"></div><div class="form-group"><label for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}"></div></div>
        <button class="btn btn-primary" type="submit">Сохранить профиль</button>
    </form>
</div>
<div class="manager-card"><h2>Сменить пароль</h2><form method="POST" action="{{ route('manager.profile.password') }}" data-submit-loading>@csrf @method('PUT')
    <div class="manager-form-grid"><div class="form-group"><label for="current_password">Текущий пароль</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required></div><div class="form-group"><label for="password">Новый пароль</label><input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required></div><div class="form-group"><label for="password_confirmation">Повторите новый пароль</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div></div>
    <button class="btn btn-primary" type="submit">Изменить пароль</button>
</form></div>
@endsection
