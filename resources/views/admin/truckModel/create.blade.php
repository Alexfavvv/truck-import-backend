@extends('admin.layouts.main')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Создание новой модели грузовика</h2>
            </div>
            <div class="card-body">
                {{-- Блок для вывода общих ошибок формы --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Блок успешного сохранения -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form action="{{ route('admin.truck-models.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Поле "Производитель" --}}
                    <div class="form-group">
                        <label for="brand_id">Производитель <span class="text-danger">*</span></label>
                        <select class="form-control @error('truckModel.brand_id') is-invalid @enderror" id="brand_id" name="truckModel[brand_id]" required>
                            <option value="">Выберите производителя</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('truckModel.brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('truckModel.brand_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Название модели" --}}
                    <div class="form-group">
                        <label for="title">Название модели <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('truckModel.title') is-invalid @enderror"
                               id="title" name="truckModel[title]"
                               value="{{ old('truckModel.title') }}" required>
                        @error('truckModel.title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Slug" --}}
                    <div class="form-group">
                        <label for="slug">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('truckModel.slug') is-invalid @enderror"
                               id="slug" name="truckModel[slug]"
                               value="{{ old('truckModel.slug') }}" required>
                        @error('truckModel.slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Изображение" --}}
                    <div class="form-group">
                        <label for="image">Изображение</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('truckModel.image') is-invalid @enderror"
                                   id="image" name="truckModel[image]">
                            <label class="custom-file-label" for="image" data-browse="Выбрать">Выберите файл...</label>
                        </div>
                        @error('truckModel.image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Допустимые форматы: jpg, png, gif, svg, webp. Макс. размер: 2МБ.</small>
                    </div>

                    {{-- Поле "Описание" --}}
                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea class="form-control @error('truckModel.description') is-invalid @enderror"
                                  id="description" name="truckModel[description]"
                                  rows="3">{{ old('truckModel.description') }}</textarea>
                        @error('truckModel.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Контент" --}}
                    <div class="form-group">
                        <label for="content">Контент</label>
                        <textarea class="form-control @error('truckModel.content') is-invalid @enderror"
                                  id="content" name="truckModel[content]"
                                  rows="6">{{ old('truckModel.content') }}</textarea>
                        @error('truckModel.content')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h5 class="mb-3"><i class="fas fa-search"></i> SEO-настройки</h5>
                    <p class="text-muted"><small>Эти поля будут сохранены в виде JSON в поле `content`.</small></p>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-h1">Meta H1</label>
                                <input type="text" class="form-control @error('meta.meta_h1') is-invalid @enderror"
                                       id="meta-h1" name="meta[meta_h1]"
                                       value="{{ old('meta.meta_h1') }}">
                                @error('meta.meta_h1')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-title">Meta Title</label>
                                <input type="text" class="form-control @error('meta.meta_title') is-invalid @enderror"
                                       id="meta-title" name="meta[meta_title]"
                                       value="{{ old('meta.meta_title') }}">
                                @error('meta.meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-description">Meta Description</label>
                                <textarea class="form-control @error('meta.meta_description') is-invalid @enderror"
                                          id="meta-description" name="meta[meta_description]"
                                          rows="1">{{ old('meta.meta_description') }}</textarea>
                                @error('meta.meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between border-top pt-3">
                        <a href="{{ route('admin.truck-models.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Сохранить модель
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Скрипт для отображения имени файла в поле загрузки --}}
    <script>
        document.getElementById('image').addEventListener('change', function(event) {
            var fileName = event.target.files[0].name;
            var label = event.target.nextElementSibling;
            label.innerText = fileName;
        });
    </script>
@endpush
