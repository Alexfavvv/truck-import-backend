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
        .messenger-field-card {
            background: #f9f9fb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 18px;
            transition: all 0.2s ease;
        }
        .messenger-field-card:focus-within {
            border-color: #B19448;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(177, 148, 72, 0.12);
        }
        .messenger-icon-badge {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.1rem;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .bg-max { background: linear-gradient(135deg, #005FF9 0%, #0042B3 100%); }
        .bg-whatsapp { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); }
        .bg-telegram { background: linear-gradient(135deg, #0088CC 0%, #006699 100%); }

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
                    <i class="fas fa-cog text-warning mr-2"></i>Настройки сайта
                </h2>
            </div>
            <div>
                <span class="badge badge-warning px-3 py-2 rounded-pill text-dark font-weight-normal" style="font-size: 0.9rem;">
                    <i class="fas fa-globe mr-1"></i> Глобальные конфигурации
                </span>
            </div>
        </div>

        <form action="{{ route('admin.pages.update', $page) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-share-alt text-warning mr-2"></i>
                                <span>Ссылки на мессенджеры в шапке сайта</span>
                            </div>
                            <small class="text-light opacity-75">Отображаются в верхней панели ПК и мобильной версий</small>
                        </div>

                        <div class="card-body p-4">
                            <div class="alert alert-light border rounded-lg p-3 mb-4 d-flex align-items-center">
                                <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                                <div>
                                    <strong class="d-block text-dark">Инструкция по настройке контактов</strong>
                                    <small class="text-muted">Указывайте полные URL-адреса (начиная с <code>https://</code>). Если оставить поле пустым, соответствующий мессенджер будет скрыт из шапки сайта.</small>
                                </div>
                            </div>

                            @php
                                $messengers = $page->settings['messengers'] ?? [];
                            @endphp

                            <!-- MAX -->
                            <div class="messenger-field-card">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="messenger-icon-badge bg-max">
                                        <i class="fas fa-comment"></i>
                                    </span>
                                    <div>
                                        <strong class="text-dark d-block">Мессенджер MAX</strong>
                                        <small class="text-muted">Ссылка на ваш профиль или чат в MAX</small>
                                    </div>
                                </div>
                                <div class="input-group shadow-sm mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-link text-muted"></i></span>
                                    </div>
                                    <input type="url" name="messengers[max_link]" id="max_link" class="form-control border-left-0" value="{{ $messengers['max_link'] ?? '' }}" placeholder="https://max.mail.ru/username">
                                </div>
                            </div>

                            <!-- WhatsApp -->
                            <div class="messenger-field-card">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="messenger-icon-badge bg-whatsapp">
                                        <i class="fab fa-whatsapp"></i>
                                    </span>
                                    <div>
                                        <strong class="text-dark d-block">WhatsApp</strong>
                                        <small class="text-muted">Формат прямых ссылок: <code>https://wa.me/79006044614</code></small>
                                    </div>
                                </div>
                                <div class="input-group shadow-sm mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-link text-muted"></i></span>
                                    </div>
                                    <input type="url" name="messengers[whatsapp_link]" id="whatsapp_link" class="form-control border-left-0" value="{{ $messengers['whatsapp_link'] ?? '' }}" placeholder="https://wa.me/79006044614">
                                </div>
                            </div>

                            <!-- Telegram -->
                            <div class="messenger-field-card">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="messenger-icon-badge bg-telegram">
                                        <i class="fab fa-telegram-plane"></i>
                                    </span>
                                    <div>
                                        <strong class="text-dark d-block">Telegram</strong>
                                        <small class="text-muted">Формат ссылок: <code>https://t.me/имя_пользователя</code></small>
                                    </div>
                                </div>
                                <div class="input-group shadow-sm mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-link text-muted"></i></span>
                                    </div>
                                    <input type="url" name="messengers[telegram_link]" id="telegram_link" class="form-control border-left-0" value="{{ $messengers['telegram_link'] ?? '' }}" placeholder="https://t.me/username">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Панель сохранения -->
                    <div class="save-bar d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                            <i class="fas fa-times mr-1"></i> Отмена
                        </a>
                        <button type="submit" class="btn btn-gold shadow">
                            <i class="fas fa-save mr-2"></i> Сохранить настройки
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
