@extends('admin.layouts.main')

@section('content')
    <div class="container mt-4">

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show m-3">
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

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="fas fa-edit"></i> Редактирование бренда "{{ $brand->title }}"</h2>
            </div>


            <div class="card-body">
                <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-3">
                                @if($brand->image_path)
                                    <img src="{{ $brand->image_url }}"
                                         class="img-thumbnail mb-3"
                                         alt="Изображение бренда"
                                         style="max-height: 200px; cursor: pointer;"
                                         data-toggle="modal"
                                         data-target="#imageModal">
                                @else
                                    <div class="bg-light p-4 text-muted rounded mb-3">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p class="mb-0">Изображение отсутствует</p>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="brand-image">Новое изображение</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="brand-image" name="brand[image]">
                                    <label class="custom-file-label" for="brand-image">Выберите файл</label>
                                </div>
                                <small class="form-text text-muted">JPG, PNG, SVG, WEBP (макс. 2MB)</small>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="brand-title">Название бренда *</label>
                                <input type="text" class="form-control @error('brand.title') is-invalid @enderror"
                                       id="brand-title" name="brand[title]"
                                       value="{{ old('brand.title', $brand->title) }}" required>
                                @error('brand.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="brand-slug">Slug (URL) *</label>
                                <input type="text" class="form-control @error('brand.slug') is-invalid @enderror"
                                       id="brand-slug" name="brand[slug]"
                                       value="{{ old('brand.slug', $brand->slug) }}" required>
                                @error('brand.slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Должен быть уникальным</small>
                            </div>
                        </div>
                    </div>

                    {{-- Поле "Какие типы деталей производит?" --}}
                    <div class="form-group">
                        <label for="brand-part_types">Производимые типы деталей</label>
                        <select class="form-control select2 @error('partTypes') is-invalid @enderror"
                                id="brand-part_types" name="partTypes[]" multiple="multiple">
                            @php
                                $selectedPartTypes = old('partTypes', $brand->partTypes->pluck('id')->toArray());
                            @endphp
                            @foreach($partTypes as $partType)
                                <option value="{{ $partType->id }}" {{ in_array($partType->id, $selectedPartTypes) ? 'selected' : '' }}>
                                    {{ $partType->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('partTypes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="brand-description">Описание</label>
                        <textarea class="form-control @error('brand.description') is-invalid @enderror"
                                  id="brand-description" name="brand[description]"
                                  rows="3">{{ old('brand.description', $brand->description) }}</textarea>
                        @error('brand.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="brand-content">Контент</label>
                        <textarea class="form-control @error('brand.content') is-invalid @enderror"
                                  id="brand-content" name="brand[content]"
                                  rows="6">{{ old('brand.content', $brand->content) }}</textarea>
                        @error('brand.content')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h5 class="mb-3"><i class="fas fa-search"></i> SEO Настройки</h5>
                    @php
                        $meta = json_decode($brand->content, true) ?? [];
                    @endphp

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-h1">Meta H1</label>
                                <input type="text" class="form-control @error('meta.meta_h1') is-invalid @enderror"
                                       id="meta-h1" name="meta[meta_h1]"
                                       value="{{ old('meta.meta_h1', $brand->metatags->meta_h1 ?? '') }}">
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
                                       value="{{ old('meta.meta_title', $brand->metatags->meta_title ?? '') }}">
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
                                          rows="1">{{ old('meta.meta_description', $brand->metatags->meta_description ?? '') }}</textarea>
                                @error('meta.meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between border-top pt-3">
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Отмена
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary text-white">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($brand->image_path)
        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Просмотр изображения</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ $brand->image_url }}" class="img-fluid" alt="Изображение бренда">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    {{-- Скрипт для отображения имени файла в поле загрузки --}}
    <script>
        $(document).ready(function() {
            // Инициализация Select2
            $('#brand-part_types').select2({
                placeholder: "Выберите производимые типы деталей",
            });
        });

        document.getElementById('brand-image').addEventListener('change', function(event) {
            var fileName = event.target.files[0].name;
            var label = event.target.nextElementSibling;
            label.innerText = fileName;
        });
    </script>
@endpush
