@extends('admin.layouts.main')

@section('content')
    <div class="container mt-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Список товаров</h2>
            <div>
                <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Добавить новый товар
                </a>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Массовый импорт
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 mr-3">
                    <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline w-50">
                        <div class="input-group w-100">
                            <input type="text" name="search" class="form-control" placeholder="Поиск по артикулу, названию..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary" title="Сбросить поиск">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <small class="text-muted">Всего записей: {{ $products->total() }}</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Артикул</th>
                            <th scope="col">Изображение</th>
                            <th scope="col">Название</th>
                            <th scope="col">Бренд</th>
                            <th scope="col">Тип запчасти</th>
                            <th scope="col">Цена</th>
                            <th scope="col">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>
                                    @if($product->image_path)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->title }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    @else
                                        <i class="far fa-image text-muted" style="font-size: 2em;"></i>
                                    @endif
                                </td>
                                <td>{{ $product->title }}</td>
                                <td>{{ $product->brand->title ?? '—' }}</td>
                                <td>{{ $product->partType->title ?? '—' }}</td>
                                <td>{{ number_format($product->price, 2, ',', ' ') }} ₽</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger ml-1" data-toggle="modal" data-target="#deleteModal" data-action="{{ route('admin.products.destroy', $product->id) }}" onclick="setDeleteAction(this)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Записей не найдено.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- Модальное окно для подтверждения удаления --}}
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
@endsection

@push('scripts')
    <script>
        function setDeleteAction(button) {
            document.getElementById('deleteForm').action = button.getAttribute('data-action');
        }
    </script>
@endpush
