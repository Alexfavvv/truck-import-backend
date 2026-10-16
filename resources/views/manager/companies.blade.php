@extends('manager.layout')

@section('title', 'Компании')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header"><h1 class="card-title">Закреплённые компании</h1></div>
        <div class="card-body">
            @if($companies->isEmpty())
                <div class="alert alert-light text-center mb-0">Компании пока не назначены. Обратитесь к администратору.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead><tr><th>Компания</th><th>ID заказчика</th><th>Контакт</th></tr></thead>
                        <tbody>
                            @foreach($companies as $company)
                                <tr>
                                    <td><a href="{{ route('manager.companies.show', $company) }}">{{ $company->company ?? $company->full_name }}</a></td>
                                    <td>{{ $company->customer_id ?? '—' }}</td>
                                    <td>{{ $company->full_name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $companies->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
