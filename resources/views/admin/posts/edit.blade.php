@extends('admin.layouts.main')

@section('content')
    <style>
        .admin-page-header {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid #eaeaea;
            margin-bottom: 24px;
        }
        .admin-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #eaeaea;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .admin-card-header {
            background: #1E1E20;
            color: #ffffff;
            padding: 18px 24px;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .form-control-lg-custom {
            font-size: 1.25rem;
            font-weight: 600;
            padding: 12px 18px;
            border-radius: 12px;
        }
        .save-bar {
            position: sticky;
            bottom: 20px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 16px 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            border: 1px solid #e2e8f0;
            margin-top: 24px;
        }
        .btn-gold {
            background: #B19448;
            color: #ffffff;
            font-weight: 600;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .btn-gold:hover {
            background: #967d3b;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(177, 148, 72, 0.35);
        }
        .cover-preview-box {
            background: #f9f9fb;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            position: relative;
            transition: all 0.2s ease;
        }
        .cover-preview-box:hover {
            border-color: #B19448;
            background: #fdfbf7;
        }
        .cover-img-thumb {
            max-height: 220px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>

    <div class="container-fluid pt-4 px-4">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-lg mb-4" role="alert" style="background: #fce8e6; color: #c5221f;">
                <h6 class="font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Ошибки при сохранении:</h6>
                <ul class="mb-0 pl-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Шапка раздела -->
        <div class="admin-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-sm btn-outline-secondary mb-2 rounded-pill px-3">
                    <i class="fas fa-arrow-left mr-1"></i> Назад к списку статей
                </a>
                <h2 class="h3 font-weight-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-edit text-warning mr-2"></i> Редактирование статьи #{{ $post->id }}
                </h2>
            </div>
            <div>
                <a href="/blog/{{ $post->slug }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3" title="Посмотреть на сайте">
                    <i class="fas fa-external-link-alt mr-1"></i> Просмотр на сайте
                </a>
            </div>
        </div>

        <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Левая колонка: Контент -->
                <div class="col-lg-8">
                    <!-- Карта основного содержимого -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-pen-nib text-warning mr-2"></i>
                                <span>Основное содержимое статьи</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-group mb-4">
                                <label for="title" class="font-weight-bold text-dark">Заголовок статьи <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control form-control-lg-custom @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}" placeholder="Введите заголовок статьи..." required>
                            </div>

                            <div class="form-group mb-4">
                                <label for="slug" class="font-weight-bold text-dark">ЧПУ (URL Slug)</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-link"></i></span>
                                    </div>
                                    <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $post->slug) }}" placeholder="testovaya-statya">
                                </div>
                                <small class="form-text text-muted">Ссылка для публикации на сайте: <code>/blog/{{ $post->slug }}</code></small>
                            </div>

                            <div class="form-group mb-4">
                                <label for="excerpt" class="font-weight-bold text-dark">Краткий анонс статьи</label>
                                <textarea name="excerpt" id="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror" placeholder="Краткое описание статьи для списка и карточек...">{{ old('excerpt', $post->excerpt) }}</textarea>
                            </div>

                            @include('admin.posts.partials.related-product-field')

                            <div class="form-group mb-0">
                                <label for="summernote" class="font-weight-bold text-dark">Полный текст статьи <span class="text-danger">*</span></label>
                                <textarea name="content" id="summernote" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $post->content) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Метатеги -->
                    <div class="admin-card">
                        <div class="admin-card-header bg-secondary">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-search text-warning mr-2"></i>
                                <span>SEO Оптимизация (Метатеги)</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-group mb-3">
                                <label for="meta_title" class="font-weight-bold text-dark">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title', $post->meta_title) }}" placeholder="Заголовок для поисковой выдачи">
                            </div>

                            <div class="form-group mb-3">
                                <label for="meta_description" class="font-weight-bold text-dark">Meta Description</label>
                                <textarea name="meta_description" id="meta_description" rows="2" class="form-control" placeholder="Описание для Яндекса и Google...">{{ old('meta_description', $post->meta_description) }}</textarea>
                            </div>

                            <div class="form-group mb-0">
                                <label for="meta_keywords" class="font-weight-bold text-dark">Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="Ключевые слова через запятую">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Правая колонка: Публикация и Обложка -->
                <div class="col-lg-4">
                    <!-- Настройки публикации -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-globe text-warning mr-2"></i>
                                <span>Публикация</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="custom-control custom-switch custom-switch-lg mb-4">
                                <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold cursor-pointer text-dark" for="is_published" style="font-size: 1.05rem;">
                                    Опубликована на сайте
                                </label>
                            </div>

                            <div class="form-group mb-4">
                                <label for="published_at" class="font-weight-bold text-dark">Дата и время публикации</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light text-muted"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="datetime-local" name="published_at" id="published_at" class="form-control" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-lg mb-3 small text-muted border">
                                <div class="mb-1"><i class="far fa-eye mr-2 text-warning"></i> Просмотров: <strong class="text-dark">{{ $post->views_count }}</strong></div>
                                <div class="mb-1"><i class="far fa-user mr-2 text-primary"></i> Автор: <strong class="text-dark">{{ $post->user ? $post->user->name : 'Администратор' }}</strong></div>
                                <div><i class="far fa-clock mr-2 text-info"></i> Создана: <strong class="text-dark">{{ $post->created_at ? $post->created_at->format('d.m.Y H:i') : '—' }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- Обложка статьи -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-image text-warning mr-2"></i>
                                <span>Обложка статьи</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="cover-preview-box mb-3">
                                @if($post->image_url)
                                    <div id="currentImageContainer" class="mb-2">
                                        <img id="imagePreview" src="{{ $post->image_url }}" alt="Обложка" class="cover-img-thumb mb-2">
                                        <div class="custom-control custom-checkbox text-danger mt-2">
                                            <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                                            <label class="custom-control-label font-weight-bold" style="cursor:pointer;" for="remove_image">
                                                <i class="fas fa-trash-alt mr-1"></i> Удалить обложку
                                            </label>
                                        </div>
                                    </div>
                                @else
                                    <div id="imagePreviewContainer" class="mb-2" style="display: none;">
                                        <img id="imagePreview" src="#" alt="Предпросмотр" class="cover-img-thumb">
                                    </div>
                                    <div id="noImagePlaceholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                        <span class="d-block font-weight-bold text-dark">Загрузить обложку</span>
                                        <small class="text-muted d-block mb-2">Рекомендуемый размер: 800x500px</small>
                                    </div>
                                @endif
                            </div>

                            <div class="custom-file mb-2">
                                <input type="file" name="cover_image" class="custom-file-input" id="cover_image" accept="image/*" onchange="previewFile(this)">
                                <label class="custom-file-label text-truncate" for="cover_image">Выберите файл...</label>
                            </div>
                            <small class="text-muted d-block text-center">JPG, PNG, WEBP, GIF. До 5 МБ.</small>
                        </div>
                    </div>
                </div>

                <!-- Панель сохранения -->
                <div class="col-12">
                    <div class="save-bar d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                            <i class="fas fa-times mr-1"></i> Отмена
                        </a>
                        <button type="submit" class="btn btn-gold shadow">
                            <i class="fas fa-save mr-2"></i> Сохранить изменения
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Введите текст статьи...',
                tabsize: 2,
                height: 380,
                lang: 'ru-RU',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });

        function previewFile(input) {
            var file = input.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                    $('#imagePreviewContainer').slideDown();
                    $('#currentImageContainer').show();
                    $('#noImagePlaceholder').hide();
                }
                reader.readAsDataURL(file);
                
                var fileName = input.value.split('\\').pop();
                $(input).siblings('.custom-file-label').addClass("selected").html(fileName);
            }
        }
    </script>
@endpush
