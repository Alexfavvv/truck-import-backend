@extends('manager.layout')

@section('title', 'Поиск деталей')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header"><h1 class="card-title">Поиск деталей</h1></div>
        <div class="card-body">
            <form method="GET" class="form-row align-items-end mb-4" role="search">
                <div class="form-group col-md-8">
                    <label for="q">Артикул или название</label>
                    <input class="form-control" id="q" name="q" value="{{ $query }}" placeholder="Артикул или название детали">
                </div>
                <div class="form-group col-md-auto"><button class="btn btn-primary">Искать</button></div>
            </form>

            @if($products === null)
                <div class="alert alert-light mb-0">Введите артикул или название детали.</div>
            @elseif($products->isEmpty())
                <div class="alert alert-light mb-0">Товары не найдены.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead><tr><th>Артикул</th><th>Наименование</th><th>Бренд</th><th>Цена</th></tr></thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->title }}</td>
                                    <td>{{ $product->brand->title ?? '—' }}</td>
                                    <td>{{ number_format($product->price, 2, ',', ' ') }} ₽</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $products->links() }}</div>
                <a class="btn btn-primary mt-3" href="{{ route('manager.orders.create', ['q' => $query]) }}">Создать заказ по найденным товарам</a>
            @endif
        </div>
    </div>
</div>
@endsection
