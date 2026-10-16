@extends('manager.layout')
@section('title', 'Компании')
@section('content')
<div class="manager-card"><h1>Закреплённые компании</h1>
    @if($companies->isEmpty())<p class="manager-empty">Компании пока не назначены. Обратитесь к администратору.</p>
    @else<div class="table-responsive"><table class="table"><thead><tr><th>Компания</th><th>ID заказчика</th><th>Контакт</th></tr></thead><tbody>
        @foreach($companies as $company)<tr><td><a href="{{ route('manager.companies.show', $company) }}">{{ $company->company ?? $company->full_name }}</a></td><td>{{ $company->customer_id ?? '—' }}</td><td>{{ $company->full_name ?? '—' }}</td></tr>@endforeach
    </tbody></table></div>{{ $companies->links() }}@endif
</div>
@endsection
