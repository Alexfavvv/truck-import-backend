@extends('customer.layouts.app')

@section('content')
<div class="orders-page">
    @if(session('success'))
        <div class="orders-page__alert orders-page__alert--success" role="alert">
            {!! nl2br(e(session('success'))) !!}
            <button type="button" class="orders-page__alert-close" data-dismiss="alert" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="orders-page__card">
        <div class="orders-page__card-header">
            <a href="{{ route('customer.managers.create') }}" class="orders-page__btn orders-page__btn--success orders-page__btn--create">
                <i class="fas fa-plus"></i><span class="orders-page__btn-text">Добавить менеджера</span>
            </a>
        </div>

        <header class="orders-page__section-header">
            <h1 class="orders-page__title">Менеджеры компании</h1>
        </header>

        <div class="orders-page__filters-wrap">
            <form method="GET" class="orders-page__filters">
                @if(request('search'))
                    <a href="{{ route('customer.managers.index') }}" class="orders-page__btn orders-page__btn--outline">
                        <i class="fas fa-times"></i><span class="orders-page__btn-text">Сбросить</span>
                    </a>
                @endif
                <div class="orders-page__search-group">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="orders-page__search-input"
                           placeholder="Поиск по ФИО, email, телефону">
                    <button type="submit" class="orders-page__search-submit" aria-label="Искать">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="orders-page__body">
            @if($managers->isNotEmpty())
                <div class="orders-page__table-wrap">
                    <table class="orders-page__table">
                        <thead>
                            <tr>
                                <th class="orders-page__th orders-page__th--name">ФИО</th>
                                <th class="orders-page__th orders-page__th--author">Телефон</th>
                                <th class="orders-page__th orders-page__th--author">Email</th>
                                <th class="orders-page__th orders-page__th--orders">Заказов</th>
                                <th class="orders-page__th orders-page__th--actions"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($managers as $manager)
                            <tr class="orders-page__row">
                                <td class="orders-page__td orders-page__td--name">
                                    <a href="{{ route('customer.managers.show', $manager) }}" class="orders-page__link">
                                        {{ $manager->full_name ?? $manager->name ?? '—' }}
                                    </a>
                                </td>
                                <td class="orders-page__td orders-page__td--author">{{ $manager->phone ?? '—' }}</td>
                                <td class="orders-page__td orders-page__td--author">{{ $manager->email ?? '—' }}</td>
                                <td class="orders-page__td orders-page__td--orders">{{ $manager->orders_count ?? 0 }}</td>
                                <td class="orders-page__td orders-page__td--actions">
                                    <a href="{{ route('customer.managers.show', $manager) }}"
                                       class="orders-page__btn orders-page__btn--check orders-page__btn--check-inline"
                                       title="Открыть">
                                        <span class="orders-page__btn-text">Открыть</span>
                                    </a>
                                    <button type="button"
                                            class="orders-page__btn orders-page__btn--reissue orders-page__btn--check-inline"
                                            data-orders-modal-open
                                            data-action="{{ route('customer.managers.destroy', $manager) }}"
                                            data-name="{{ $manager->full_name ?? $manager->name }}">
                                        <span class="orders-page__btn-text">Удалить</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="orders-page__empty">
                    <span class="orders-page__empty-icon"><i class="fas fa-users"></i></span>
                    Менеджеров пока нет.
                    <a href="{{ route('customer.managers.create') }}" class="orders-page__link">Добавить менеджера</a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="orders-page__modal" id="deleteManagerModal" aria-hidden="true">
    <div class="orders-page__modal-dialog">
        <form id="deleteManagerForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="orders-page__modal-content">
                <div class="orders-page__modal-header">
                    <h2 class="orders-page__modal-title">Удалить менеджера</h2>
                    <button type="button" class="orders-page__modal-close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="orders-page__modal-body">
                    <p class="orders-page__modal-text">Удалить менеджера <strong id="deleteManagerName"></strong>?</p>
                    <p class="orders-page__modal-warning">Все его заказы будут сохранены.</p>
                </div>
                <div class="orders-page__modal-footer">
                    <button type="button" class="orders-page__btn orders-page__btn--secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="orders-page__btn orders-page__btn--danger">Удалить</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var modal = document.getElementById('deleteManagerModal');
    var form = document.getElementById('deleteManagerForm');
    var nameEl = document.getElementById('deleteManagerName');
    if (!modal || !form) return;
    document.querySelectorAll('[data-orders-modal-open]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            form.action = this.getAttribute('data-action');
            if (nameEl) nameEl.textContent = this.getAttribute('data-name') || '';
            modal.classList.add('orders-page__modal--open');
            modal.setAttribute('aria-hidden', 'false');
        });
    });
    function closeModal() {
        modal.classList.remove('orders-page__modal--open');
        modal.setAttribute('aria-hidden', 'true');
    }
    modal.querySelectorAll('[data-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
})();
</script>
@endpush
