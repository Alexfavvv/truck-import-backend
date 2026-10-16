@extends('manager.layout')

@section('title', 'Создать заказ')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="card-title">Создать заказ</h1>
            <a href="{{ route('manager.orders.index') }}">← К заказам</a>
        </div>
        <div class="card-body">
            @if($companies->isEmpty())
                <div class="alert alert-light mb-0">Для создания заказа нужна закреплённая компания. Обратитесь к администратору.</div>
            @else
                <form method="GET" action="{{ route('manager.orders.create') }}" class="form-row align-items-end mb-4" role="search">
                    <div class="form-group col-md-8">
                        <label for="q">Найти товары по названию или артикулу</label>
                        <input id="q" name="q" class="form-control" value="{{ $query }}" placeholder="Название или артикул">
                    </div>
                    <div class="form-group col-md-auto"><button class="btn btn-outline-secondary">Найти</button></div>
                </form>

                @if($query === '')
                    <div class="alert alert-light mb-0">Найдите товары, чтобы добавить их в заказ.</div>
                @elseif($products->isEmpty())
                    <div class="alert alert-light mb-0">По запросу товары не найдены.</div>
                @else
                    <form method="POST" action="{{ route('manager.orders.store', ['q' => $query]) }}" data-submit-loading>
                        @csrf
                        <div class="form-group">
                            <label for="company_admin_id">Компания заказчика</label>
                            <select id="company_admin_id" name="company_admin_id" class="form-control" required>
                                <option value="">Выберите компанию</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_admin_id') == $company->id ? 'selected' : '' }}>{{ $company->company ?? $company->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-muted">Укажите количество для нужных товаров. Цена берётся из каталога при создании заказа.</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Артикул</th><th>Товар</th><th>Цена</th><th>Количество</th></tr></thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>{{ $product->sku }}</td>
                                            <td>{{ $product->title }}</td>
                                            <td>{{ number_format($product->price, 2, ',', ' ') }} ₽</td>
                                            <td>
                                                <label class="sr-only" for="quantity-{{ $product->id }}">Количество {{ $product->title }}</label>
                                                <input id="quantity-{{ $product->id }}" type="number" min="0" max="999" class="form-control" style="max-width:7rem" name="quantities[{{ $product->id }}]" value="{{ old('quantities.'.$product->id, 0) }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-primary">Создать заказ</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
