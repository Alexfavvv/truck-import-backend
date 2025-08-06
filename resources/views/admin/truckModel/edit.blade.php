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
                <h2 class="mb-0"><i class="fas fa-edit"></i> Редактирование модели "{{ $truckModel->title }}"</h2>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.truck-models.update', $truckModel) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-3">
                                @if($truckModel->image_path)
                                    <img src="{{ $truckModel->image_url }}"
                                         class="img-thumbnail mb-3"
                                         alt="Изображение модели"
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
                                <label for="truck-model-image">Новое изображение</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="truck-model-image" name="truckModel[image]">
                                    <label class="custom-file-label" for="truck-model-image">Выберите файл</label>
                                </div>
                                <small class="form-text text-muted">JPG, PNG, SVG, WEBP (макс. 2MB)</small>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="brand-id">Производитель <span class="text-danger">*</span></label>
                                <select class="form-control @error('truckModel.brand_id') is-invalid @enderror" id="brand-id" name="truckModel[brand_id]" required>
                                    <option value="">Выберите производителя</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('truckModel.brand_id', $truckModel->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('truckModel.brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="truck-model-title">Название модели *</label>
                                <input type="text" class="form-control @error('truckModel.title') is-invalid @enderror"
                                       id="truck-model-title" name="truckModel[title]"
                                       value="{{ old('truckModel.title', $truckModel->title) }}" required>
                                @error('truckModel.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="truck-model-slug">Slug (URL) *</label>
                                <input type="text" class="form-control @error('truckModel.slug') is-invalid @enderror"
                                       id="truck-model-slug" name="truckModel[slug]"
                                       value="{{ old('truckModel.slug', $truckModel->slug) }}" required>
                                @error('truckModel.slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Должен быть уникальным</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="truck-model-description">Описание</label>
                        <textarea class="form-control @error('truckModel.description') is-invalid @enderror"
                                  id="truck-model-description" name="truckModel[description]"
                                  rows="3">{{ old('truckModel.description', $truckModel->description) }}</textarea>
                        @error('truckModel.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поле "Контент" --}}
                    <hr>
                    <h4 class="mb-3">Содержимое страницы</h4>

                    {{-- Первый блок контента (3 штуки) --}}
                    <h5 class="mb-3">Первый блок контента (3 штуки)</h5>
                    @for ($i = 0; $i < 3; $i++)
                        @php
                            $firstPlaceData = old("truckModel.content.first__place.$i.0", $truckModel->content['first__place'][$i][0] ?? '');
                            $firstPlaceHtml = old("truckModel.content.first__place.$i.1", $truckModel->content['first__place'][$i][1] ?? '');
                        @endphp
                        <div class="form-group border p-3 rounded mb-3">
                            <label>Блок {{ $i + 1 }}</label>
                            <input type="text" class="form-control mb-2"
                                   name="truckModel[content][first__place][{{ $i }}][0]"
                                   value="{{ $firstPlaceData }}"
                                   placeholder="Заголовок">
                            <textarea class="form-control summernote"
                                      name="truckModel[content][first__place][{{ $i }}][1]"
                                      placeholder="Содержимое">{{ $firstPlaceHtml }}</textarea>
                        </div>
                    @endfor

                    {{-- Второй блок контента (3 штуки) --}}
                    <h5 class="mb-3 mt-4">Второй блок контента (3 штуки)</h5>
                    @for ($i = 0; $i < 3; $i++)
                        @php
                            $secondPlaceData = old("truckModel.content.second__place.$i.0", $truckModel->content['second__place'][$i][0] ?? '');
                            $secondPlaceHtml = old("truckModel.content.second__place.$i.1", $truckModel->content['second__place'][$i][1] ?? '');
                        @endphp
                        <div class="form-group border p-3 rounded mb-3">
                            <label>Блок {{ $i + 1 }}</label>
                            <input type="text" class="form-control mb-2"
                                   name="truckModel[content][second__place][{{ $i }}][0]"
                                   value="{{ $secondPlaceData }}"
                                   placeholder="Заголовок">
                            <textarea class="form-control summernote"
                                      name="truckModel[content][second__place][{{ $i }}][1]"
                                      placeholder="Содержимое">{{ $secondPlaceHtml }}</textarea>
                        </div>
                    @endfor

                    {{-- FAQ --}}
                    <h5 class="mb-3 mt-4">FAQ</h5>
                    <div class="form-group">
                        <label for="faq__title">Заголовок FAQ</label>
                        <input type="text" class="form-control" id="faq__title"
                               name="truckModel[content][faq__title]"
                               value="{{ old('truckModel.content.faq__title', $truckModel->content['faq__title'] ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>Вопросы и ответы (FAQ)</label>
                        <div id="faq-container">
                            @php
                                // Проверяем наличие old() данных, иначе используем данные из модели
                                $faqData = old('truckModel.content.faq__data', $truckModel->content['faq__data'] ?? []);
                                $faqCount = count($faqData);
                            @endphp

                            @if ($faqCount > 0)
                                @foreach($faqData as $index => $faqItem)
                                    <div class="faq-item mb-3 p-3 border rounded">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control"
                                                   name="truckModel[content][faq__data][{{ $index }}][0]"
                                                   value="{{ $faqItem[0] ?? '' }}"
                                                   placeholder="Вопрос">
                                            <div class="input-group-append">
                                                <button class="btn btn-danger remove-faq-field" type="button">Удалить</button>
                                            </div>
                                        </div>
                                        <textarea class="form-control summernote"
                                                  name="truckModel[content][faq__data][{{ $index }}][1]"
                                                  placeholder="Ответ">{{ $faqItem[1] ?? '' }}</textarea>
                                    </div>
                                @endforeach
                            @else
                                {{-- Если данных нет, показываем одно пустое поле --}}
                                <div class="faq-item mb-3 p-3 border rounded">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="truckModel[content][faq__data][0][0]" placeholder="Вопрос">
                                        <div class="input-group-append">
                                            <button class="btn btn-danger remove-faq-field" type="button">Удалить</button>
                                        </div>
                                    </div>
                                    <textarea class="form-control summernote" name="truckModel[content][faq__data][0][1]" placeholder="Ответ"></textarea>
                                </div>
                            @endif
                        </div>
                        <button class="btn btn-success mt-2 add-faq-field" type="button" data-target="#faq-container">Добавить вопрос</button>
                    </div>

                    <hr>
                    <h5 class="mb-3"><i class="fas fa-search"></i> SEO Настройки</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="meta-h1">Meta H1</label>
                                <input type="text" class="form-control @error('meta.meta_h1') is-invalid @enderror"
                                       id="meta-h1" name="meta[meta_h1]"
                                       value="{{ old('meta.meta_h1', $truckModel->metatags->meta_h1 ?? '') }}">
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
                                       value="{{ old('meta.meta_title', $truckModel->metatags->meta_title ?? '') }}">
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
                                          rows="1">{{ old('meta.meta_description', $truckModel->metatags->meta_description ?? '') }}</textarea>
                                @error('meta.meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between border-top pt-3">
                        <a href="{{ route('admin.truck-models.index') }}" class="btn btn-outline-secondary">
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

    @if($truckModel->image_path)
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
                        <img src="{{ $truckModel->image_url }}" class="img-fluid" alt="Изображение модели">
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
    {{-- Скрипты для Summernote --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация Summernote
            function initializeSummernote(selector) {
                $(selector).summernote({
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
            }

            // Инициализируем Summernote для ВСЕХ существующих полей с классом '.summernote'
            initializeSummernote('.summernote');

            // -------------------------------------------------------------
            // Функции для FAQ
            // -------------------------------------------------------------
            let faqContainer = document.getElementById('faq-container');
            let addFaqBtn = document.querySelector('.add-faq-field');

            function updateFaqIndexes() {
                document.querySelectorAll('#faq-container .faq-item').forEach((item, index) => {
                    item.querySelectorAll('input, textarea').forEach(field => {
                        let name = field.getAttribute('name');
                        if (name) {
                            name = name.replace(/truckModel\[content\]\[faq__data\]\[\d+\]/, `truckModel[content][faq__data][${index}]`);
                            field.setAttribute('name', name);
                        }
                    });
                });
            }

            function addFaqField() {
                const index = faqContainer.children.length;
                const newFaqItem = document.createElement('div');
                newFaqItem.classList.add('faq-item', 'mb-3', 'p-3', 'border', 'rounded');
                newFaqItem.innerHTML = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="truckModel[content][faq__data][${index}][0]" placeholder="Вопрос">
                <div class="input-group-append">
                    <button class="btn btn-danger remove-faq-field" type="button">Удалить</button>
                </div>
            </div>
            <textarea class="form-control summernote" name="truckModel[content][faq__data][${index}][1]" placeholder="Ответ"></textarea>`;
                faqContainer.appendChild(newFaqItem);
                // Инициализируем Summernote только для нового поля
                initializeSummernote($(newFaqItem).find('.summernote'));
            }

            function removeFaqField(event) {
                if (event.target.classList.contains('remove-faq-field')) {
                    // Предотвращаем удаление, если это единственный элемент
                    if (faqContainer.children.length > 1) {
                        const itemToRemove = event.target.closest('.faq-item');
                        $(itemToRemove).find('.summernote').summernote('destroy');
                        itemToRemove.remove();
                        updateFaqIndexes();
                    } else {
                        alert('Нельзя удалить последний вопрос!');
                    }
                }
            }

            addFaqBtn.addEventListener('click', addFaqField);
            faqContainer.addEventListener('click', removeFaqField);

            // Скрипт для отображения имени файла в поле загрузки
            document.getElementById('truck-model-image').addEventListener('change', function(event) {
                var fileName = event.target.files[0].name;
                var label = event.target.nextElementSibling;
                label.innerText = fileName;
            });
        });
    </script>
@endpush
