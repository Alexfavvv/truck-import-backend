@extends('manager.layout')

@section('title', 'Дашборд')

@section('content')
<div class="dashboard-page">
    <h1 class="dashboard-page__title">Дашборд</h1>

    <div class="dashboard-page__tiles">
        <a href="{{ route('manager.search') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-search" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Поиск деталей</span>
        </a>
        <a href="{{ route('manager.orders.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-shopping-cart" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Заказы</span>
        </a>
        <a href="{{ route('manager.invoices.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-file-invoice" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Счета на оплату</span>
        </a>
        <a href="{{ route('manager.companies.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Компании</span>
        </a>
        <a href="{{ route('manager.profile') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Мой профиль</span>
        </a>
    </div>

</div>
@endsection
