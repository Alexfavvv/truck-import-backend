@extends('manager.layout')
@section('title', 'Поиск товаров')
@section('content')
<div class="manager-card">
    <h1>Поиск товаров</h1>
    <form method="GET" class="manager-actions" role="search"><label for="q" class="sr-only">Артикул или название</label><input class="form-control" style="max-width:480px" id="q" name="q" value="{{ $query }}" placeholder="Артикул или название детали"><button class="btn btn-primary">Искать</button></form>
    @if($products === null)<p class="manager-empty">Введите артикул или название детали.</p>
    @elseif($products->isEmpty())<p class="manager-empty">Товары не найдены.</p>
    @else
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Артикул</th><th>Наименование</th><th>Бренд</th><th>Цена</th></tr></thead><tbody>
        @foreach($products as $product)<tr><td>{{ $product->sku }}</td><td>{{ $product->title }}</td><td>{{ $product->brand->title ?? '—' }}</td><td>{{ number_format($product->price, 2, ',', ' ') }} ₽</td></tr>@endforeach
    </tbody></table></div>{{ $products->links() }}
    <a class="btn btn-primary mt-3" href="{{ route('manager.orders.create', ['q' => $query]) }}">Создать заказ по найденным товарам</a>
    @endif
</div>
@endsection
