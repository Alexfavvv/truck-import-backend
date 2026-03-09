@extends('customer.layouts.app')

@section('content')
<div class="dashboard-page">
    <h1 class="dashboard-page__title">Дашборд</h1>

    <div class="dashboard-page__tiles">
        <a href="{{ route('customer.orders.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-shopping-cart" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Заказы</span>
        </a>
        <a href="{{ route('customer.search.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-search" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Поиск деталей</span>
        </a>
        <a href="{{ route('customer.invoices.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-file-invoice" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Счета на оплату</span>
        </a>
        <a href="{{ route('customer.profile') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Мой профиль</span>
        </a>
    </div>
</div>
@endsection
