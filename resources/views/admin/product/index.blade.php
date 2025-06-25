@extends('admin.layouts.main')

@section('content')
    <div class="container mt-4">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Добавить товар
        </a>

        <table class="table table-bordered">
            <thead style="background-color: #007bff; color: #fff;">
            <tr>
                <th>Артикул</th>
                <th>Название</th>
                <th>Категория</th>
                <th>Марка</th>
                <th>Производитель</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr>
                    <td class="font-monospace">{{ $product->sku }}</td>
                    <td>{{ $product->title }}</td>
                    <td>{{ $product->category->title ?? '—' }}</td>
                    <td>{{ $product->category->parent->title ?? '—' }}</td>
                    <td>{{ $product->brand->title ?? '—' }}</td>
                    <td>{{ $product->price ?? '—'}}</td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <button class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteModal"
                                data-action="{{ route('admin.products.destroy', $product->id) }}"
                                onclick="setDeleteAction(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Пагинация -->
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted">
            Показано с {{ $products->firstItem() }} по {{ $products->lastItem() }} из {{ $products->total() }} товаров
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                {{ $products->onEachSide(1)->links('pagination::bootstrap-4') }}
            </ul>
        </nav>
    </div>


    <!-- Модальное окно удаления (остаётся без изменений) -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Удаление товара</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Вы точно хотите удалить этот товар?</p>
                        <p>Это действие нельзя отменить.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setDeleteAction(button) {
            document.getElementById('deleteForm').action = button.getAttribute('data-action');
        }
    </script>
@endsection
