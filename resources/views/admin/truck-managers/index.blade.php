@extends('admin.layouts.main')

@section('content')
<section class="content p-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1>Менеджеры Трак Импорт</h1>
        <a href="{{ route('admin.truck-managers.create') }}" class="btn btn-primary">Добавить менеджера</a>
    </div>
    @if(session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>ФИО</th><th>Логин</th><th>Компании</th><th>Состояние</th><th></th></tr></thead>
            <tbody>
            @forelse($managers as $manager)
                <tr>
                    <td>{{ $manager->full_name }}</td>
                    <td>{{ $manager->login }}</td>
                    <td>
                        @forelse($manager->assignedCompanies as $company)
                            <span class="badge badge-light mr-1">{{ $company->company ?? $company->full_name }}</span>
                        @empty — @endforelse
                    </td>
                    <td>{{ $manager->is_active ? 'Активен' : 'Отключён' }}</td>
                    <td><a href="{{ route('admin.truck-managers.edit', $manager) }}">Открыть</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Менеджеров пока нет.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $managers->links() }}
    </div></div>
</section>
@endsection
