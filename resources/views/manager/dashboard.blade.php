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

    <section class="card mt-4" aria-labelledby="manager-statistics-title">
        <div class="card-header">
            <h2 id="manager-statistics-title" class="card-title">Статистика</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="info-box mb-0">
                        <span class="info-box-icon bg-dark"><i class="fas fa-building" aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Компании</span>
                            <span class="info-box-number">{{ $companiesCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="info-box mb-0">
                        <span class="info-box-icon bg-dark"><i class="fas fa-shopping-cart" aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Заказы</span>
                            <span class="info-box-number">{{ $ordersCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box mb-0">
                        <span class="info-box-icon bg-dark"><i class="fas fa-file-invoice" aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Счета</span>
                            <span class="info-box-number">{{ $invoicesCount }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
