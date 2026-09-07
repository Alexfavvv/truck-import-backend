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
        .page-item-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #eaeaea;
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }
        .page-item-card:hover {
            border-color: #B19448;
            box-shadow: 0 8px 24px rgba(177, 148, 72, 0.15);
            transform: translateY(-2px);
        }
        .page-icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(177, 148, 72, 0.12);
            color: #B19448;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .page-icon-wrapper-settings {
            background: rgba(30, 30, 32, 0.08);
            color: #1E1E20;
        }
        .btn-gold {
            background: #B19448;
            color: #ffffff;
            font-weight: 600;
            border: none;
            padding: 10px 24px;
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

        <!-- Шапка раздела -->
        <div class="admin-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="h3 font-weight-bold mb-1 text-dark d-flex align-items-center">
                    <i class="fas fa-layer-group text-warning mr-2"></i> Управление страницами и разделами
                </h2>
                <small class="text-muted">Редактирование баннеров главной страницы и глобальных контактов сайта</small>
            </div>
            <div>
                <span class="badge badge-dark px-3 py-2 rounded-pill font-weight-normal" style="font-size: 0.9rem;">
                    Всего разделов: {{ count($pages) }}
                </span>
            </div>
        </div>

        <div class="row">
            @forelse($pages as $p)
                <div class="col-12 col-lg-6">
                    <div class="page-item-card d-flex flex-column justify-content-between h-100">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center" style="gap: 14px;">
                                    <div class="page-icon-wrapper {{ $p->slug === 'site_settings' ? 'page-icon-wrapper-settings' : '' }}">
                                        @if($p->slug === 'site_settings')
                                            <i class="fas fa-cog"></i>
                                        @else
                                            <i class="fas fa-home"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-weight-bold text-dark mb-1" style="font-size: 1.2rem;">{{ $p->name }}</h4>
                                        <code class="small text-muted bg-light px-2 py-1 rounded">slug: {{ $p->slug }}</code>
                                    </div>
                                </div>

                                @if($p->slug === 'site_settings')
                                    <span class="badge badge-info px-3 py-2 rounded-pill">
                                        <i class="fas fa-globe mr-1"></i> Контакты
                                    </span>
                                @else
                                    <span class="badge badge-warning px-3 py-2 rounded-pill text-dark">
                                        <i class="fas fa-images mr-1"></i> Баннеры
                                    </span>
                                @endif
                            </div>

                            <p class="text-muted mb-4 small" style="line-height: 1.6;">
                                @if($p->slug === 'site_settings')
                                    Управление глобальными ссылками на мессенджеры (MAX, WhatsApp, Telegram) в верхней панели ПК и мобильной версий сайта.
                                @else
                                    Настройка главного слайдера: загрузка изображений для ПК, планшетов и смартфонов, ссылки переходов и вызов модальных окон.
                                @endif
                            </p>
                        </div>

                        <div class="border-top pt-3 d-flex align-items-center justify-content-between">
                            <small class="text-muted">
                                <i class="far fa-clock mr-1"></i> Изменено: {{ $p->updated_at->format('d.m.Y H:i') }}
                            </small>

                            @if($p->slug === 'site_settings')
                                <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-dark shadow-sm font-weight-bold px-4 py-2" style="border-radius: 10px;">
                                    <i class="fas fa-cog mr-1"></i> Открыть настройки
                                </a>
                            @else
                                <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-gold shadow-sm px-4 py-2">
                                    <i class="fas fa-sliders-h mr-1"></i> Настроить баннеры
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center p-5 rounded-lg border-0 shadow-sm">
                        <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
                        <h5>Страницы не найдены</h5>
                        <p class="mb-0 text-muted">Пожалуйста, выполните сидер базы данных для инициализации стандартных страниц.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
