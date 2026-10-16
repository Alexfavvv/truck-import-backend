@extends('manager.layout')
@section('title', 'Главная')
@section('content')
<div class="manager-card"><h1>Здравствуйте, {{ auth()->user()->full_name ?? auth()->user()->name }}</h1><p>Здесь собраны заказы и счета закреплённых за вами компаний.</p></div>
<div class="manager-grid">
    <a href="{{ route('manager.companies.index') }}" class="manager-stat text-dark"><strong>{{ $companiesCount }}</strong>Компании</a>
    <a href="{{ route('manager.orders.index') }}" class="manager-stat text-dark"><strong>{{ $ordersCount }}</strong>Заказы</a>
    <a href="{{ route('manager.invoices.index') }}" class="manager-stat text-dark"><strong>{{ $invoicesCount }}</strong>Счета</a>
</div>
@endsection
