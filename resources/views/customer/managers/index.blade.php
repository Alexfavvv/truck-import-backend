@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Менеджеры компании</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('customer.managers.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i>Добавить менеджера
                </a>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                {!! nl2br(e(session('success'))) !!}
            </div>
        @endif
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr><th>ФИО</th><th>Телефон</th><th>Email</th><th>Заказов</th><th>Действия</th></tr>
                    </thead>
                    <tbody>
                    @forelse($managers as $manager)
                        <tr>
                            <td>
                                <a href="{{ route('customer.managers.show', $manager) }}">
                                    {{ $manager->full_name ?? $manager->name }}
                                </a>
                            </td>
                            <td>{{ $manager->phone ?? '—' }}</td>
                            <td>{{ $manager->email ?? '—' }}</td>
                            <td>{{ $manager->orders_count }}</td>
                            <td>
                                <a href="{{ route('customer.managers.show', $manager) }}"
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-danger ml-1"
                                        data-toggle="modal" data-target="#deleteModal"
                                        data-action="{{ route('customer.managers.destroy', $manager) }}"
                                        data-name="{{ $manager->full_name ?? $manager->name }}"
                                        onclick="setAction(this)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Менеджеров пока нет.
                                <a href="{{ route('customer.managers.create') }}">Добавить?</a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
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
                    <h5 class="modal-title text-danger">Удалить менеджера</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Удалить менеджера <strong id="mgrName"></strong>?</p>
                    <p class="text-muted">Все его заказы будут сохранены.</p>
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
function setAction(btn) {
    document.getElementById('deleteForm').action = btn.dataset.action;
    document.getElementById('mgrName').textContent = btn.dataset.name;
}
</script>
@endpush
