@extends('admin.layouts.main')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @php session()->forget('success'); @endphp
    @endif

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Создание нового товара</h2>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card-body">
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product-brand_id">Бренд <span class="text-danger">*</span></label>
                                <select class="form-control @error('product.brand_id') is-invalid @enderror"
                                        id="product-brand_id" name="product[brand_id]" required>
                                    <option value="">Выберите бренд</option>
                                    @foreach($brands as $brand)
                                        <option
                                            value="{{ $brand->id }}" {{ old('product.brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product.brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product-category_id">Категория <span class="text-danger">*</span></label>
                                <select class="form-control @error('product.category_id') is-invalid @enderror"
                                        id="product-category_id" name="product[category_id]" required>
                                    <option value="">Выберите категорию</option>
                                    @foreach($categories as $category)
                                        <option
                                            value="{{ $category->id }}" {{ old('product.category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->parent->title }} - {{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product.category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product-sku">Артикул <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product.sku') is-invalid @enderror"
                                       id="product-sku" name="product[sku]"
                                       value="{{ old('product.sku') }}" required>
                                @error('product.sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product-title">Название товара <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product.title') is-invalid @enderror"
                                       id="product-title" name="product[title]"
                                       value="{{ old('product.title') }}" required>
                                @error('product.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product-description">Описание</label>
                        <textarea class="form-control @error('product.description') is-invalid @enderror"
                                  id="product-description" name="product[description]"
                                  rows="4">{{ old('product.description') }}</textarea>
                        @error('product.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product-price">Цена <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product.price') is-invalid @enderror"
                                       id="product-price" name="product[price]"
                                       value="{{ old('product.price') }}" required>
                                @error('product.price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product-quantity">Количество <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('product.quantity') is-invalid @enderror"
                                       id="product-quantity" name="product[quantity]"
                                       value="{{ old('product.quantity', 0) }}" required min="0">
                                @error('product.quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product-delivery">Срок доставки</label>
                                <input type="text" class="form-control @error('product.delivery') is-invalid @enderror"
                                       id="product-delivery" name="product[delivery]"
                                       value="{{ old('product.delivery') }}">
                                @error('product.delivery')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product-image">Изображение товара</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('product.image') is-invalid @enderror"
                                   id="product-image" name="product[image]">
                            <label class="custom-file-label" for="product-image">Выберите файл</label>
                        </div>
                        @error('product.image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Допустимые форматы: jpeg, png, jpg, gif, svg, webp</small>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label>Характеристики товара</label>
                        <div id="characteristics-repeater">
                            @foreach(old('characteristics', []) as $index => $char)
                                <div class="characteristic-item d-flex mb-2">
                                    <input type="text" name="characteristics[{{ $index }}][title]"
                                           value="{{ old("characteristics.$index.title") }}" placeholder="Название"
                                           class="form-control">
                                    <input type="text" name="characteristics[{{ $index }}][description]"
                                           value="{{ old("characteristics.$index.description") }}"
                                           placeholder="Описание" class="form-control ml-2">
                                    <button type="button" class="btn btn-danger ml-2 remove-characteristic">×</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-characteristic" class="btn btn-success mt-2">Добавить
                            характеристику
                        </button>
                    </div>

                    <hr>
                    <h5 class="mb-3"><i class="fas fa-search"></i> SEO Настройки</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-meta_h1">Meta H1</label>
                                <input type="text" class="form-control @error('meta.meta_h1') is-invalid @enderror"
                                       id="meta-meta_h1" name="meta[meta_h1]"
                                       value="{{ old('meta.meta_h1') }}">
                                @error('meta.meta_h1')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-meta_title">Meta Title</label>
                                <input type="text" class="form-control @error('meta.meta_title') is-invalid @enderror"
                                       id="meta-meta_title" name="meta[meta_title]"
                                       value="{{ old('meta.meta_title') }}">
                                @error('meta.meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-meta_description">Meta Description</label>
                                <textarea class="form-control @error('meta.meta_description') is-invalid @enderror"
                                          id="meta-meta_description" name="meta[meta_description]"
                                          rows="1">{{ old('meta.meta_description') }}</textarea>
                                @error('meta.meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between border-top pt-3">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Сохранить товар
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Добавляет и Удаляет поля Характеристик
        document.addEventListener('DOMContentLoaded', function () {
            let repeater = document.getElementById('characteristics-repeater');
            let addBtn = document.getElementById('add-characteristic');

            function updateIndexes() {
                document.querySelectorAll('.characteristic-item').forEach((item, index) => {
                    item.querySelectorAll('input').forEach(input => {
                        let name = input.getAttribute('name');
                        name = name.replace(/\d+/, index); // Обновляем индекс в имени
                        input.setAttribute('name', name);
                    });
                });
            }

            function addCharacteristic() {
                const index = repeater.children.length;
                const newField = document.createElement('div');
                newField.classList.add('characteristic-item', 'd-flex', 'mb-2');
                newField.dataset.index = index;

                newField.innerHTML = `
                    <input type="text" name="characteristics[${index}][title]" placeholder="Название" class="form-control">
                    <input type="text" name="characteristics[${index}][description]" placeholder="Описание" class="form-control ml-2">
                    <button type="button" class="btn btn-danger ml-2 remove-characteristic">×</button>
                `;
                repeater.appendChild(newField);
            }

            function removeCharacteristic(event) {
                if (event.target.classList.contains('remove-characteristic')) {
                    event.target.closest('.characteristic-item').remove();
                    updateIndexes();
                }
            }

            addBtn.addEventListener('click', addCharacteristic);
            repeater.addEventListener('click', removeCharacteristic);
        });
    </script>

@endsection


