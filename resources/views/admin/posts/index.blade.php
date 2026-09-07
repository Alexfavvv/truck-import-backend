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
        .filter-bar {
            background: #f9f9fb;
            border-bottom: 1px solid #eaeaea;
            padding: 16px 24px;
        }
        .post-table {
            margin-bottom: 0;
        }
        .post-table th {
            background: #f4f5f7;
            color: #4b5563;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            border-top: none;
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 18px;
        }
        .post-table td {
            padding: 16px 18px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f4;
        }
        .post-table tbody tr:hover {
            background-color: #fcfcfd;
        }
        .post-cover-thumb {
            width: 64px;
            height: 48px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .post-cover-placeholder {
            width: 64px;
            height: 48px;
            background: #eef1f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 1.2rem;
            border: 1px dashed #cbd5e1;
        }
        .btn-gold {
            background: #B19448;
            color: #ffffff;
            font-weight: 600;
            border: none;
            padding: 10px 22px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .btn-gold:hover {
            background: #967d3b;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(177, 148, 72, 0.35);
        }
        .status-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .status-badge-published {
            background: #e6f4ea;
            color: #137333;
        }
        .status-badge-published:hover {
            background: #ceebd6;
        }
        .status-badge-draft {
            background: #f1f3f4;
            color: #5f6368;
        }
        .status-badge-draft:hover {
            background: #e2e5e8;
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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-lg mb-4" role="alert" style="background: #fce8e6; color: #c5221f;">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Шапка раздела -->
        <div class="admin-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="h3 font-weight-bold mb-1 text-dark d-flex align-items-center">
                    <i class="fas fa-newspaper text-warning mr-2"></i> Управление блогом и статьями
                </h2>
                <small class="text-muted">Публикация новостей, технических обзоров и материалов</small>
            </div>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-gold shadow">
                <i class="fas fa-plus-circle mr-1"></i> Написать статью
            </a>
        </div>

        <div class="admin-card">
            <!-- Фильтры и поиск -->
            <div class="filter-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
                <form action="{{ route('admin.posts.index') }}" method="GET" class="form-inline flex-wrap gap-2">
                    <div class="input-group shadow-sm mr-2 mb-2 mb-md-0" style="min-width: 280px;">
                        <input type="text" name="search" class="form-control border-right-0" placeholder="Поиск по статьям..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-white border border-left-0 bg-white text-muted" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <select name="status" class="form-control shadow-sm mr-2 mb-2 mb-md-0" onchange="this.form.submit()">
                        <option value="">Все статусы</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Опубликованные</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Черновики</option>
                    </select>

                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary rounded-pill px-3 mb-2 mb-md-0" title="Сбросить фильтры">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    @endif
                </form>

                <div class="text-muted font-weight-600 small">
                    <i class="fas fa-layer-group mr-1"></i> Всего статей: <span class="badge badge-dark px-2 py-1">{{ $posts->total() }}</span>
                </div>
            </div>

            <!-- Таблица -->
            <div class="table-responsive">
                <table class="table post-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 80px;">Обложка</th>
                            <th>Заголовок статьи</th>
                            <th style="width: 130px;">Статус</th>
                            <th style="width: 100px;"><i class="fas fa-eye" title="Просмотры"></i> Просмотры</th>
                            <th style="width: 160px;">Дата публикации</th>
                            <th style="width: 110px; text-align: right;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td class="font-weight-bold text-muted">{{ $post->id }}</td>
                                <td>
                                    @if($post->image_url)
                                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="post-cover-thumb">
                                    @else
                                        <div class="post-cover-placeholder">
                                            <i class="far fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="font-weight-bold text-dark text-decoration-none h6 mb-1 d-block">
                                        {{ $post->title }}
                                    </a>
                                    <div class="d-flex align-items-center gap-2">
                                        <code class="small text-muted bg-light px-2 py-1 rounded">/blog/{{ $post->slug }}</code>
                                    </div>
                                    @if($post->excerpt)
                                        <small class="text-muted d-block mt-1 text-truncate" style="max-width: 420px;">
                                            {{ $post->excerpt }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.posts.toggle-publish', $post) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($post->is_published)
                                            <button type="submit" class="status-badge status-badge-published" title="Нажмите, чтобы снять с публикации">
                                                <i class="fas fa-check-circle mr-1"></i> Опубликован
                                            </button>
                                        @else
                                            <button type="submit" class="status-badge status-badge-draft" title="Нажмите, чтобы опубликовать">
                                                <i class="fas fa-pen mr-1"></i> Черновик
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="text-muted small">
                                    <i class="far fa-eye mr-1 text-warning"></i>{{ $post->views_count }}
                                </td>
                                <td class="text-muted small">
                                    {{ $post->published_at ? $post->published_at->format('d.m.Y H:i') : '—' }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-warning rounded-circle p-2" title="Редактировать" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger rounded-circle p-2 ml-1" data-toggle="modal" data-target="#deletePostModal" data-action="{{ route('admin.posts.destroy', $post) }}" onclick="setDeleteAction(this)" title="Удалить" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-newspaper fa-3x mb-3 text-muted d-block opacity-50"></i>
                                    <h5 class="font-weight-bold">Статей пока нет</h5>
                                    <p class="small mb-3">Нажмите «Написать статью», чтобы создать первую публикацию в блоге.</p>
                                    <a href="{{ route('admin.posts.create') }}" class="btn btn-gold btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Создать запись
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($posts->hasPages())
                <div class="p-3 border-top bg-light">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deletePostModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Подтверждение удаления</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    Вы уверены, что хотите полностью удалить эту статью из блога? Это действие необратимо.
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-dismiss="modal">Отмена</button>
                    <form id="deletePostForm" action="" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4 rounded-pill font-weight-bold">Удалить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function setDeleteAction(button) {
            var action = button.getAttribute('data-action');
            document.getElementById('deletePostForm').setAttribute('action', action);
        }
    </script>
@endpush
