@extends('manager.layout')
@section('title', 'Создать заказ')
@section('content')
<div class="manager-card"><h1>Создать заказ</h1>
    @if($companies->isEmpty())<p class="manager-empty">Для создания заказа нужна закреплённая компания. Обратитесь к администратору.</p>
    @else
        <form method="GET" action="{{ route('manager.orders.create') }}" class="manager-actions" role="search"><input name="q" class="form-control" style="max-width:450px" value="{{ $query }}" placeholder="Найти товары по названию или артикулу" aria-label="Поиск товаров"><button class="btn btn-outline-secondary">Найти</button></form>
        @if($query === '')<p class="manager-empty">Найдите товары, чтобы добавить их в заказ.</p>
        @elseif($products->isEmpty())<p class="manager-empty">По запросу товары не найдены.</p>
        @else
        <form method="POST" action="{{ route('manager.orders.store', ['q' => $query]) }}" data-submit-loading>
            @csrf
            <div class="form-group"><label for="company_admin_id">Компания заказчика</label><select id="company_admin_id" name="company_admin_id" class="form-control" required><option value="">Выберите компанию</option>@foreach($companies as $company)<option value="{{ $company->id }}" {{ old('company_admin_id') == $company->id ? 'selected' : '' }}>{{ $company->company ?? $company->full_name }}</option>@endforeach</select></div>
            <p class="text-muted">Укажите количество для нужных товаров. Цена берётся из каталога при создании заказа.</p>
            <div class="table-responsive"><table class="table"><thead><tr><th>Артикул</th><th>Товар</th><th>Цена</th><th>Количество</th></tr></thead><tbody>
                @foreach($products as $product)<tr><td>{{ $product->sku }}</td><td>{{ $product->title }}</td><td>{{ number_format($product->price, 2, ',', ' ') }} ₽</td><td><label class="sr-only" for="quantity-{{ $product->id }}">Количество {{ $product->title }}</label><input id="quantity-{{ $product->id }}" type="number" min="0" max="999" class="form-control" style="width:100px" name="quantities[{{ $product->id }}]" value="{{ old('quantities.'.$product->id, 0) }}"></td></tr>@endforeach
            </tbody></table></div>
            <button type="submit" class="btn btn-primary">Создать заказ</button>
        </form>
        @endif
    @endif
</div>
@endsection
