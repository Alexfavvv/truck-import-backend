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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .admin-card:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.07);
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
        .admin-card-header .banner-badge {
            background: rgba(177, 148, 72, 0.2);
            color: #d4af37;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            border: 1px solid rgba(177, 148, 72, 0.4);
        }
        .upload-device-card {
            background: #f9f9fb;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.2s ease;
            position: relative;
        }
        .upload-device-card:hover {
            border-color: #B19448;
            background: #fdfbf7;
        }
        .device-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #1E1E20;
            margin-bottom: 12px;
        }
        .action-type-pill {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .action-type-pill label {
            flex: 1;
            padding: 14px 16px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            cursor: pointer;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            color: #4b5563;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 0;
        }
        .action-type-pill input[type="radio"]:checked + label {
            border-color: #1E1E20;
            background: #1E1E20;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(30, 30, 32, 0.2);
        }
        .action-type-pill input[type="radio"] {
            display: none;
        }
        .preview-img-box {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            background: #eef1f6;
            display: inline-block;
            border: 1px solid #e2e8f0;
        }
        .preview-img-box img {
            max-height: 80px;
            max-width: 140px;
            object-fit: contain;
            display: block;
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
            margin-top: 30px;
            margin-bottom: 30px;
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
    </style>

    <div class="container-fluid pt-4 px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-lg mb-4" role="alert" style="background: #e6f4ea; color: #137333;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Шапка страницы -->
        <div class="admin-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-outline-secondary mb-2 rounded-pill px-3">
                    <i class="fas fa-arrow-left mr-1"></i> Назад к списку страниц
                </a>
                <h2 class="h3 font-weight-bold mb-0 text-dark">
                    <i class="fas fa-sliders-h text-warning mr-2"></i>Настройки страницы: {{ $page->name }}
                </h2>
            </div>
            <div>
                <span class="badge badge-dark px-3 py-2 rounded-pill font-weight-normal" style="font-size: 0.9rem;">
                    Slug: <code>{{ $page->slug }}</code>
                </span>
            </div>
        </div>

        <form action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="alert alert-light border rounded-lg shadow-sm d-flex align-items-center p-3 mb-4">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong class="d-block text-dark">Управление баннерами главной страницы</strong>
                            <small class="text-muted">Загружайте разные варианты баннеров для компьютеров, планшетов и телефонов. Для каждого баннера можно привязать ссылку перехода или вызов модального окна обратной связи.</small>
                        </div>
                    </div>
                </div>

                @php
                    $banners = $page->settings['banners'] ?? [];
                @endphp

                @foreach($banners as $banner)
                    <div class="col-12">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-images text-warning mr-2"></i>
                                    <span>Баннер #{{ $banner['id'] }}: {{ $banner['name'] }}</span>
                                </div>
                                <span class="banner-badge"><i class="fas fa-layer-group mr-1"></i> Слайд {{ $banner['id'] }}</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    {{-- Левая колонка: Изображения --}}
                                    <div class="col-lg-7 border-right pr-lg-4 mb-4 mb-lg-0">
                                        <h5 class="font-weight-bold mb-3 text-dark d-flex align-items-center">
                                            <i class="fas fa-desktop text-primary mr-2"></i> Изображения для разных экранов
                                        </h5>
                                        
                                        @foreach([
                                            'image_pc' => ['label' => 'Для ПК (Desktop)', 'icon' => 'fa-desktop', 'desc' => '1920x480px / 1200x400px'],
                                            'image_tablet' => ['label' => 'Для планшетов (Tablet)', 'icon' => 'fa-tablet-alt', 'desc' => '800x400px / 768x300px'],
                                            'image_mobile' => ['label' => 'Для мобильных (Mobile)', 'icon' => 'fa-mobile-alt', 'desc' => '480x320px / 360x240px']
                                        ] as $field => $info)
                                            <div class="upload-device-card mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="device-badge">
                                                        <i class="fas {{ $info['icon'] }} text-muted"></i> {{ $info['label'] }}
                                                    </span>
                                                    <small class="text-muted"><i class="far fa-question-circle mr-1"></i> {{ $info['desc'] }}</small>
                                                </div>

                                                @if(!empty($banner[$field]))
                                                    <div class="p-2 border rounded bg-white d-flex align-items-center justify-content-between mb-2 shadow-sm">
                                                        <div class="d-flex align-items-center">
                                                            <div class="preview-img-box mr-3">
                                                                <img src="{{ Storage::disk('public')->url($banner[$field]) }}" alt="Preview">
                                                            </div>
                                                            <div>
                                                                <small class="font-weight-bold text-dark d-block">Загруженный файл:</small>
                                                                <small class="text-muted"><code>{{ basename($banner[$field]) }}</code></small>
                                                            </div>
                                                        </div>
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="remove_{{ $banner['id'] }}_{{ $field }}" name="banners[{{ $banner['id'] }}][remove_{{ $field }}]" value="1">
                                                            <label class="custom-control-label text-danger font-weight-bold" style="cursor:pointer;" for="remove_{{ $banner['id'] }}_{{ $field }}">
                                                                <i class="fas fa-trash-alt mr-1"></i> Удалить
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="file_{{ $banner['id'] }}_{{ $field }}" name="banners[{{ $banner['id'] }}][{{ $field }}]" accept=".jpg,.jpeg,.png,.webp">
                                                    <label class="custom-file-label text-truncate" for="file_{{ $banner['id'] }}_{{ $field }}">Выберите файл (.jpg, .png, .webp)</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Правая колонка: Настройка кликов --}}
                                    <div class="col-lg-5 pl-lg-4">
                                        <h5 class="font-weight-bold mb-3 text-dark d-flex align-items-center">
                                            <i class="fas fa-mouse-pointer text-warning mr-2"></i> Поведение при клике
                                        </h5>

                                        <label class="font-weight-bold small text-uppercase text-muted d-block mb-2">Выберите тип взаимодействия:</label>
                                        
                                        <div class="action-type-pill">
                                            <div>
                                                <input type="radio" id="link_type_url_{{ $banner['id'] }}" name="banners[{{ $banner['id'] }}][link_type]" value="link" {{ ($banner['link_type'] ?? 'link') === 'link' ? 'checked' : '' }} onchange="toggleLinkUrl({{ $banner['id'] }}, 'link')">
                                                <label for="link_type_url_{{ $banner['id'] }}">
                                                    <i class="fas fa-link"></i> Переход по ссылке
                                                </label>
                                            </div>
                                            <div>
                                                <input type="radio" id="link_type_modal_{{ $banner['id'] }}" name="banners[{{ $banner['id'] }}][link_type]" value="callback" {{ ($banner['link_type'] ?? '') === 'callback' ? 'checked' : '' }} onchange="toggleLinkUrl({{ $banner['id'] }}, 'callback')">
                                                <label for="link_type_modal_{{ $banner['id'] }}">
                                                    <i class="fas fa-phone-alt"></i> Заказать звонок
                                                </label>
                                            </div>
                                        </div>

                                        <div id="url_container_{{ $banner['id'] }}" style="{{ ($banner['link_type'] ?? 'link') === 'callback' ? 'display: none;' : '' }}">
                                            <div class="form-group">
                                                <label for="link_url_{{ $banner['id'] }}" class="font-weight-bold">URL-адрес для перехода:</label>
                                                <div class="input-group shadow-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-light"><i class="fas fa-globe text-muted"></i></span>
                                                    </div>
                                                    <input type="text" class="form-control" id="link_url_{{ $banner['id'] }}" name="banners[{{ $banner['id'] }}][link_url]" value="{{ $banner['link_url'] ?? '' }}" placeholder="Например: /catalog или https://...">
                                                </div>
                                                <small class="form-text text-muted mt-2">
                                                    <i class="fas fa-lightbulb text-warning mr-1"></i> Можно указывать внутренние ссылки (например, <code>/catalog/filters</code>) или внешние сайты.
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-warning border-0 shadow-sm p-3 rounded-lg" id="modal_info_{{ $banner['id'] }}" style="{{ ($banner['link_type'] ?? 'link') === 'link' ? 'display: none;' : '' }}; background: #fff8e6; color: #8a6d3b;">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-check-circle fa-lg mr-2 mt-1 text-warning"></i>
                                                <div>
                                                    <strong class="d-block mb-1">Режим вызова формы</strong>
                                                    <small>При клике по баннеру на сайте откроется всплывающее окно закупки/звонка.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Панель сохранения -->
                <div class="col-12">
                    <div class="save-bar d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                            <i class="fas fa-times mr-1"></i> Отмена
                        </a>
                        <button type="submit" class="btn btn-gold shadow">
                            <i class="fas fa-save mr-2"></i> Сохранить баннеры
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleLinkUrl(bannerId, type) {
            const urlContainer = document.getElementById('url_container_' + bannerId);
            const modalInfo = document.getElementById('modal_info_' + bannerId);
            
            if (type === 'callback') {
                $(urlContainer).slideUp(200);
                $(modalInfo).slideDown(200);
            } else {
                $(urlContainer).slideDown(200);
                $(modalInfo).slideUp(200);
            }
        }

        // Авто-подстановка имени выбранного файла в custom-file-input
        $(document).on('change', '.custom-file-input', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@endpush
