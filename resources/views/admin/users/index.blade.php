@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Пользователи</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Все пользователи</h3>
                @can('create', \App\Models\User::class)
                    <div>
                        <a href="{{ route('admin.users.create', ['role' => 'manager_truck']) }}"
                           class="btn btn-outline-primary btn-sm mr-1">
                            <i class="fas fa-plus mr-1"></i>Менеджер ТИ
                        </a>
                        <a href="{{ route('admin.users.create', ['role' => 'admin_customer']) }}"
                           class="btn btn-outline-success btn-sm mr-1">
                            <i class="fas fa-plus mr-1"></i>Админ Заказчика
                        </a>
                        <a href="{{ route('admin.users.create', ['role' => 'manager_customer']) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-plus mr-1"></i>Менеджер Заказчика
                        </a>
                    </div>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Логин / Email</th>
                                <th>Роль</th>
                                <th>Компания / Родитель</th>
                                <th>Статус</th>
                                <th style="width:100px">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}">
                                        {{ $user->full_name ?? $user->name }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ $user->login ?? '—' }}</div>
                                    <small class="text-muted">{{ $user->email ?? '' }}</small>
                                </td>
                                <td>
                                    @php
                                        $roleColors = [
                                            'admin_truck'      => 'badge-danger',
                                            'manager_truck'    => 'badge-warning',
                                            'admin_customer'   => 'badge-primary',
                                            'manager_customer' => 'badge-secondary',
                                        ];
                                    @endphp
                                    <span class="badge {{ $roleColors[$user->role?->value] ?? 'badge-light' }}">
                                        {{ $user->role?->label() ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->company)
                                        {{ $user->company }}
                                    @elseif($user->parent)
                                        <small class="text-muted">→ {{ $user->parent->full_name ?? $user->parent->name }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-success">Активен</span>
                                    @else
                                        <span class="badge badge-danger">Деактивирован</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="btn btn-sm btn-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $user)
                                        @if(auth()->user()->isAdminTruck() || (auth()->user()->isAdminCustomer() && $user->parent_id === auth()->id()))
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="btn btn-sm btn-warning ml-1" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    @endcan
                                    @can('delete', $user)
                                        <button class="btn btn-sm btn-danger ml-1"
                                                data-toggle="modal" data-target="#deleteModal"
                                                data-action="{{ route('admin.users.destroy', $user) }}"
                                                data-name="{{ $user->full_name ?? $user->name }}"
                                                data-role="{{ $user->role?->value }}"
                                                onclick="setDeleteData(this)" title="Удалить">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    Пользователи не найдены
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Удаление пользователя
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Удалить пользователя <strong id="deleteName"></strong>?</p>
                    <div id="cascadeWarning" class="alert alert-warning" style="display:none">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <strong>Внимание!</strong> Будут удалены все прикреплённые менеджеры и все заказы компании.
                    </div>
                    <p class="text-danger mb-0"><strong>Это действие необратимо.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setDeleteData(btn) {
        document.getElementById('deleteForm').action = btn.dataset.action;
        document.getElementById('deleteName').textContent = btn.dataset.name;
        document.getElementById('cascadeWarning').style.display =
            btn.dataset.role === 'admin_customer' ? 'block' : 'none';
    }
</script>
@endpush
