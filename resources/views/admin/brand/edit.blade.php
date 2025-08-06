@extends('admin.layouts.main')

@section('content')
    <div class="container mt-4">

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

        {{-- Блок успешного сохранения --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
                {{ session('success') }}
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
                <form action="{{ route('admin.brands.update', $brand->slug) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{-- Поле "Название бренда" --}}
                    <div class="form-group">
                        <label for="brand-title">Название бренда <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('brand.title') is-invalid @enderror"
                               id="brand-title" name="brand[title]"
                               value="{{ old('brand.title', $brand->title) }}" required>
                        @error('brand.title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Slug" --}}
                    <div class="form-group">
                        <label for="brand-slug">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('brand.slug') is-invalid @enderror"
                               id="brand-slug" name="brand[slug]"
                               value="{{ old('brand.slug', $brand->slug) }}" required>
                        @error('brand.slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Какие типы деталей производит?" --}}
                    <div class="form-group">
                        <label for="brand-part_types">Производимые типы деталей</label>
                        <select class="form-control select2 @error('partTypes') is-invalid @enderror"
                                id="brand-part_types" name="partTypes[]" multiple="multiple">
                            @php
                                // Получаем ID выбранных типов деталей
                                $selectedPartTypeIds = old('partTypes', $brand->partTypes->pluck('id')->toArray());
                            @endphp
                            @foreach($partTypes as $partType)
                                <option value="{{ $partType->id }}" {{ in_array($partType->id, $selectedPartTypeIds) ? 'selected' : '' }}>
                                    {{ $partType->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('partTypes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Изображение" --}}
                    <div class="form-group">
                        <label for="brand-image">Изображение</label>
                        @if($brand->image_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $brand->image_path) }}" alt="Текущее изображение" style="max-width: 200px; height: auto;">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image">
                                    <label class="form-check-label" for="remove_image">Удалить изображение</label>
                                </div>
                            </div>
                        @endif
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('brand.image') is-invalid @enderror"
                                   id="brand-image" name="brand[image]">
                            <label class="custom-file-label" for="brand-image" data-browse="Выбрать">Выберите новый файл...</label>
                        </div>
                        @error('brand.image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Допустимые форматы: jpg, png, gif, svg, webp. Макс. размер: 2МБ.</small>
                    </div>

                    {{-- Поле "Описание" --}}
                    <div class="form-group">
                        <label for="brand-description">Описание</label>
                        <textarea class="form-control @error('brand.description') is-invalid @enderror"
                                  id="brand-description" name="brand[description]"
                                  rows="3">{{ old('brand.description', $brand->description) }}</textarea>
                        @error('brand.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Контент" --}}
                    <div class="form-group">
                        <label for="subcat__title">Заголовок подкатегории</label>
                        <input type="text" class="form-control" id="subcat__title" name="brand[content][subcat__title]" value="{{ old('brand.content.subcat__title', $brand->content['subcat__title'] ?? null) }}">
                    </div>

                    <h5 class="mb-3">Первый блок контента</h5>
                    <div class="form-group">
                        <label for="first__place-title">Заголовок</label>
                        <input type="text" id="first__place-title" class="form-control" name="brand[content][first__place][0][0]" value="{{ old('brand.content.first__place.0.0', $brand->content['first__place'][0][0] ?? null) }}" placeholder="Заголовок">

                        <label for="first__place-content">Описание</label>
                        <textarea class="form-control summernote" id="first__place-content" name="brand[content][first__place'][0][1]" placeholder="Содержимое">
                            {{ old('brand.content.first__place.0.1', $brand->content['first__place'][0][1] ?? null) }}
                        </textarea>
                    </div>

                    <h5 class="mb-3">Второй блок контента</h5>
                    <div class="form-group">
                        <label for="second__place-title">Заголовок</label>
                        <input type="text" id="second__place-title" class="form-control mb-2" name="brand[content][second__place][0][0]" value="{{ old('brand.content.second__place.0.0', $brand->content['second__place'][0][0] ?? null) }}" placeholder="Заголовок">

                        <label for="second__place-content">Описание</label>
                        <textarea class="form-control summernote" id="second__place-content" name="brand[content][second__place][0][1]" placeholder="Содержимое">
                            {{ old('brand.content.second__place.0.1', $brand->content['second__place'][0][1] ?? null) }}
                        </textarea>
                    </div>

                    <h5 class="mb-3">FAQ</h5>
                    <div class="form-group">
                        <label for="faq__title">Заголовок FAQ</label>
                        <input type="text" class="form-control" id="faq__title" name="brand[content][faq__title]" value="{{ old('brand.content.faq__title', $brand->content['faq__title'] ?? null) }}">
                    </div>

                    <div class="form-group">
                        <label>Вопросы и ответы (FAQ)</label>
                        <div id="faq-container">
                            {{-- Заполняем поля из старых данных или данных модели --}}
                            @php
                                $faqData = old('brand.content.faq__data', $brand->content['faq__data'] ?? []);
                            @endphp
                            @forelse($faqData as $index => $faqItem)
                                <div class="faq-item mb-3 p-3 border rounded">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="brand[content][faq__data][{{ $index }}][0]"
                                               value="{{ $faqItem[0] ?? null }}" placeholder="Вопрос">
                                        <div class="input-group-append">
                                            <button class="btn btn-danger remove-faq-field" type="button">Удалить</button>
                                        </div>
                                    </div>
                                    <textarea class="form-control summernote" name="brand[content][faq__data][{{ $index }}][1]"
                                              placeholder="Ответ">{{ $faqItem[1] ?? null }}</textarea>
                                </div>
                            @empty
                                {{-- Если старых данных или данных из модели нет, отображаем одно пустое поле --}}
                                <div class="faq-item mb-3 p-3 border rounded">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="brand[content][faq__data][0][0]" placeholder="Вопрос">
                                        <div class="input-group-append">
                                            <button class="btn btn-danger remove-faq-field" type="button">Удалить</button>
                                        </div>
                                    </div>
                                    <textarea class="form-control summernote" name="brand[content][faq__data][0][1]" placeholder="Ответ"></textarea>
                                </div>
                            @endforelse
                        </div>
                        <button class="btn btn-success mt-2 add-faq-field" type="button" data-target="#faq-container">Добавить вопрос</button>
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
                                       value="{{ old('meta.meta_h1', $brand->metatags->meta_h1 ?? null) }}">
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
                                       value="{{ old('meta.meta_title', $brand->metatags->meta_title ?? null) }}">
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
                                          rows="1">{{ old('meta.meta_description', $brand->metatags->meta_description ?? null) }}</textarea>
                                @error('meta.meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between border-top pt-3">
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Обновить бренд
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // -------------------------------------------------------------
            // Инициализация Summernote
            // -------------------------------------------------------------
            function initializeSummernote(selector) {
                $(selector).each(function() {
                    const initialContent = $(this).val(); // Получаем значение из textarea
                    $(this).summernote({
                        placeholder: 'Введите ответ здесь',
                        tabsize: 2,
                        height: 150,
                        toolbar: [
                            ['style', ['bold', 'italic', 'underline', 'clear']],
                            ['font', ['strikethrough', 'superscript', 'subscript']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['height', ['height']],
                            ['codeview']
                        ]
                    });

                    // Вставляем начальное содержимое в Summernote
                    if (initialContent) {
                        $(this).summernote('code', initialContent);
                    }
                });
            }

            // Инициализируем все существующие Summernote при загрузке
            initializeSummernote('.summernote');

            // Инициализация Select2
            $('#brand-part_types').select2({
                placeholder: "Выберите производимые типы деталей",
            });

            // -------------------------------------------------------------
            // Функции для FAQ
            // -------------------------------------------------------------
            let faqContainer = document.getElementById('faq-container');
            let addFaqBtn = document.querySelector('.add-faq-field');

            // Функция для обновления индексов полей
            function updateFaqIndexes() {
                document.querySelectorAll('#faq-container .faq-item').forEach((item, index) => {
                    // Обновляем индексы в input и textarea
                    item.querySelectorAll('input, textarea').forEach(field => {
                        let name = field.getAttribute('name');
                        if (name) {
                            name = name.replace(/brand\[content\]\[faq__data\]\[\d+\]/, `brand[content][faq__data][${index}]`);
                            field.setAttribute('name', name);
                        }
                    });
                });
            }

            // Функция для добавления нового поля
            function addFaqField() {
                const index = faqContainer.children.length;
                const newFaqItem = document.createElement('div');
                newFaqItem.classList.add('faq-item', 'mb-3', 'p-3', 'border', 'rounded');
                newFaqItem.innerHTML = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="brand[content][faq__data][${index}][0]" placeholder="Вопрос">
                    <div class="input-group-append">
                        <button class="btn btn-danger remove-faq-field" type="button">Удалить</button>
                    </div>
                </div>
                <textarea class="form-control summernote" name="brand[content][faq__data][${index}][1]" placeholder="Ответ"></textarea>`;
                faqContainer.appendChild(newFaqItem);
                initializeSummernote($(newFaqItem).find('.summernote'));
            }

            // Функция для удаления поля
            function removeFaqField(event) {
                if (event.target.classList.contains('remove-faq-field')) {
                    const itemToRemove = event.target.closest('.faq-item');
                    $(itemToRemove).find('.summernote').summernote('destroy');
                    itemToRemove.remove();
                    updateFaqIndexes();
                }
            }

            addFaqBtn.addEventListener('click', addFaqField);
            faqContainer.addEventListener('click', removeFaqField);

            // Удаление полей для FAQ
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('remove-faq-field')) {
                    // Перед удалением, нужно уничтожить Summernote
                    $(e.target).closest('.faq-item').find('.summernote').summernote('destroy');
                    e.target.closest('.faq-item').remove();
                }
            });

            // Скрипт для отображения имени файла в поле загрузки
            document.getElementById('brand-image').addEventListener('change', function(event) {
                var fileName = event.target.files[0].name;
                var label = event.target.nextElementSibling;
                label.innerText = fileName;
            });
        });
    </script>
@endpush
