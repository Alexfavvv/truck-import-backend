@extends('admin.layouts.main')

@section('content')
<section class="orders-page admin-compact-page admin-compact-page--managers">
    <header class="orders-page__section-header admin-compact-page__header">
        <h1 class="orders-page__title">Менеджеры Трак Импорт</h1>
        <a href="{{ route('admin.truck-managers.create') }}" class="orders-page__btn orders-page__btn--success">
            <i class="fas fa-plus" aria-hidden="true"></i> Добавить менеджера
        </a>
    </header>

    @if(session('success'))
        <div class="orders-page__alert orders-page__alert--success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="orders-page__card">
        <div class="orders-page__body">
            @if($managers->isNotEmpty())
                <div class="orders-page__table-wrap">
                    <table class="orders-page__table admin-compact-page__table">
                        <thead>
                            <tr>
                                <th class="orders-page__th orders-page__th--name">ФИО</th>
                                <th class="orders-page__th orders-page__th--login">Логин</th>
                                <th class="orders-page__th orders-page__th--companies">Компании</th>
                                <th class="orders-page__th orders-page__th--status">Состояние</th>
                                <th class="orders-page__th orders-page__th--actions"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($managers as $manager)
                                <tr class="orders-page__row">
                                    <td class="orders-page__td orders-page__td--name">{{ $manager->full_name }}</td>
                                    <td class="orders-page__td orders-page__td--login">{{ $manager->login }}</td>
                                    <td class="orders-page__td orders-page__td--companies">
                                        @forelse($manager->assignedCompanies as $company)
                                            <span class="badge badge-light mr-1">{{ $company->company ?? $company->full_name }}</span>
                                        @empty
                                            —
                                        @endforelse
                                    </td>
                                    <td class="orders-page__td orders-page__td--status">
                                        <span class="orders-page__badge orders-page__badge--{{ $manager->is_active ? 'completed' : 'cancelled' }}">
                                            {{ $manager->is_active ? 'Активен' : 'Отключён' }}
                                        </span>
                                    </td>
                                    <td class="orders-page__td orders-page__td--actions">
                                        <a href="{{ route('admin.truck-managers.edit', $manager) }}" class="orders-page__btn orders-page__btn--outline">
                                            Открыть
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($managers->hasPages())
                    <div class="orders-page__pagination">
                        {{ $managers->links() }}
                    </div>
                @endif
            @else
                <p class="orders-page__empty">Менеджеров пока нет.</p>
            @endif
        </div>
    </div>
</section>
@endsection
