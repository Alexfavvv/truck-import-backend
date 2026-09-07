@extends('admin.layouts.main')

@section('content')
<div class="dashboard-page" style="min-height: 12rem;">
    <h1 class="dashboard-page__title">Дашборд</h1>

    <div class="dashboard-page__tiles">
        <a href="{{ route('admin.search.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-search" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Поиск деталей</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-shopping-cart" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Заказы</span>
        </a>
        <a href="{{ route('admin.invoices.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-file-invoice" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Счета на оплату</span>
        </a>
        <a href="{{ route('admin.profile') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Мой профиль</span>
        </a>
        @if(!auth()->user()->isManagerTruck() && !auth()->user()->isManagerCustomer())
        <a href="{{ route('admin.users.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Профили компаний</span>
        </a>
        @endif
        <a href="{{ Route::has('admin.posts.index') ? route('admin.posts.index') : url('/admin/posts') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-newspaper" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Блог / Статьи</span>
        </a>
        <a href="{{ route('admin.pages.index') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Страницы</span>
        </a>
        <a href="{{ route('admin.pages.edit', 'site_settings') }}" class="dashboard-page__tile">
            <span class="dashboard-page__tile-icon"><i class="fas fa-cog" aria-hidden="true"></i></span>
            <span class="dashboard-page__tile-text">Настройки сайта</span>
        </a>
    </div>
</div>
@endsection
