<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Личный кабинет</title>

    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- Navbar --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item d-flex align-items-center mr-3">
                <span class="text-muted mr-2">
                    <i class="fas fa-building mr-1"></i>
                    {{ auth()->user()->company ?? (auth()->user()->full_name ?? auth()->user()->name) }}
                </span>
                <span class="badge badge-primary">{{ auth()->user()->role?->label() }}</span>
            </li>
            <li class="nav-item">
                <form action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-sign-out-alt mr-1"></i>Выйти
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    {{-- Sidebar --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('customer.dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">Трак Импорт</span>
        </a>
        <div class="sidebar">
            {{-- Профиль в sidebar --}}
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-secondary ml-1"></i>
                </div>
                <div class="info">
                    <a href="{{ route('customer.profile') }}" class="d-block" style="font-size:12px">
                        {{ auth()->user()->full_name ?? auth()->user()->name }}
                    </a>
                    @if(auth()->user()->customer_id)
                        <small class="text-secondary" style="font-size:10px">
                            ID: {{ auth()->user()->customer_id }}
                        </small>
                    @endif
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                    <li class="nav-item">
                        <a href="{{ route('customer.dashboard') }}"
                           class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Главная</p>
                        </a>
                    </li>

                    <li class="nav-header">Работа</li>

                    <li class="nav-item">
                        <a href="{{ route('customer.orders.index') }}"
                           class="nav-link {{ request()->routeIs('customer.orders*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>
                                Мои заказы
                                {{-- @if($ordersCount ?? 0) --}}
                                {{-- <span class="badge badge-primary right">{{ $ordersCount }}</span> --}}
                                {{-- @endif --}}
                            </p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('customer.invoices.index') }}"
                           class="nav-link {{ request()->routeIs('customer.invoices*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice"></i>
                            <p>Счета на оплату</p>
                        </a>
                    </li>

                    {{-- Менеджеры — только для admin_customer --}}
                    @if(auth()->user()->isAdminCustomer())
                    <li class="nav-header">Компания</li>
                    <li class="nav-item">
                        <a href="{{ route('customer.managers.index') }}"
                           class="nav-link {{ request()->routeIs('customer.managers*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Менеджеры</p>
                        </a>
                    </li>
                    @endif

                    <li class="nav-header">Аккаунт</li>
                    <li class="nav-item">
                        <a href="{{ route('customer.profile') }}"
                           class="nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Профиль</p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    {{-- Content --}}
    <div class="content-wrapper">
        @yield('content')
    </div>

    <footer class="main-footer">
        <strong>Трак Импорт</strong> &copy; {{ now()->year }}
    </footer>

    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script src="{{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<script src="{{ asset('AdminLTE/dist/js/adminlte.js') }}"></script>
@stack('scripts')
</body>
</html>
