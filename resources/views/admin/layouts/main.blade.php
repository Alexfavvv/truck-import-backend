<!DOCTYPE html>
<html lang="{{ auth()->user()?->isManagerTruck() ? 'ru' : 'en' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Truck-Import')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href=" {{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href=" {{ asset('AdminLTE/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href=" {{ asset('AdminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/css/select2.min.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/summernote/summernote-bs4.min.css') }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            overflow-x: hidden;
            /* font-family: system-ui, -apple-system, sans-serif; */
        }

        /* html {
            font-size: calc(1vw/14.4*10);
            overflow-y: scroll;
            scrollbar-width: thin;
            overflow-x: hidden;
            margin-right: calc(-1 * (100vw - 100%));
        } */

        ul,
        li {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: start;
        }

        .menu {
            display: flex;
            min-width: max-content;
            background: #141313;
            color: #fff;
            gap: 3.75rem;
        }

        .menu-submenu {
            display: none;
        }

        .menu__btn,
        .menu__link {
            display: flex;
            line-height: 120%;
            gap: 1rem;
            vertical-align: middle;
            align-items: center;
            padding: .6rem 1rem;
            font-size: 1.125rem;
            font-weight: 500;
            color: #fff;
            cursor: pointer;
            border: none;
            border-radius: .5rem .5rem 0 0;
            background: transparent;
            text-decoration: none;
        }

        .menu__btn[aria-expanded="true"] {
            border-radius: .5rem .5rem 0 0;
            background-color: #272626;
            border: none;
        }

        .menu__link:hover,
        .menu__btn:hover,
        .menu__btn[aria-expanded="true"],
        .menu__btn:hover svg,
        .menu__btn[aria-expanded="true"] svg,
        .menu__btn:hover path,
        .menu__btn[aria-expanded="true"] path {
            color: #C9AC37;
            stroke: #C9AC37;
        }

        .menu__btn-icon {
            color: inherit;
            width: .875rem;
            height: .875rem;
            transition: transform 0.3s ease;
        }

        .menu__btn[aria-expanded="true"] .menu__btn-icon {
            transform: rotate(180deg);
        }

        .menu__item {
            position: relative;
        }

        #catalog-submenu {
            max-height: 22rem;
        }

        #about-submenu {
            border-radius: 0 0 .5rem .5rem;
        }

        a[aria-current="page"] {
            font-weight: 500;
            color: #fff;
        }

        .menu .menu {
            position: absolute;
            gap: 0 1rem;
            padding: 1rem 0;
            width: 100%;
            background-color: #272626;
            border: 0;
            border-radius: 0 .5rem .5rem .5rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, auto);
            grid-template-rows: repeat(4, auto);
            grid-auto-rows: 0;
            grid-auto-flow: column dense;
            z-index: 100;
        }

        .menu .menu .menu__item:nth-child(4n+1) {
            margin-bottom: .5rem;
        }

        .menu .menu .menu__item:nth-child(4n+2),
        .menu .menu .menu__item:nth-child(4n+3) {
            margin-top: .5rem;
            margin-bottom: .5rem;
        }

        .menu .menu .menu__item:nth-child(4n+4) {
            margin-top: .5rem;
        }

        .menu .menu .menu__item:last-child {
            margin-bottom: 0 !important;
        }

        .menu[hidden] {
            display: none;
        }

        .logo {
            width: max-content;
        }

        header .logo img {
            width: 9.0625rem;
            height: auto;
        }

        .text {
            font-weight: 400;
            font-size: 1.125rem;
        }

        .stop-scrolling {
            height: 100vh;
            overflow: hidden;
        }

        body:has(dialog[open]) {
            height: 100vh;
            overflow: hidden;
        }

        dialog::backdrop {
            background: rgba(126, 126, 126, .5);
        }

        dialog {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            padding: 0;
            border: none;
            border-radius: .8rem;
            background: #141313;
            max-width: 61rem;
            width: 90%;
        }

        dialog>div.feedback {
            width: 61rem;
            min-width: 61rem;
        }

        /* Header specific styles */
        .header {
            display: flex;
            flex-direction: column;
            background-color: #141313;
            width: 100%;
            justify-content: space-between;
            z-index: 9;
        }

        .header_fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        .header__buttons {
            display: flex;
            flex-direction: row;
            gap: 0.625rem;
        }

        .header__modal_close {
            position: absolute;
            top: 2rem;
            right: 2rem;
            cursor: pointer;
        }

        .header__button {
            display: flex;
            align-items: center;
            color: #141313;
            background-color: #C9AC37;
            border: none;
            padding: 0.625rem 1.25rem;
            border-radius: 0.625rem;
            gap: 0.375rem;
            font-weight: 700;
            font-size: 1rem;
            line-height: 120%;
            cursor: pointer;
            height: max-content;
            text-decoration: none;
        }

        .header__account {
            display: flex;
            align-items: center;
            color: #141313;
            background-color: #FFFFFF14;
            border: none;
            padding: 0.625rem;
            border-radius: 0.625rem;
            gap: 0.375rem;
            font-weight: 700;
            font-size: 1rem;
            line-height: 120%;
            cursor: pointer;
            height: max-content;
            text-decoration: none;
        }

        .header__account svg {
            width: 1.5625rem;
            height: auto;
        }

        .header__button svg {
            width: 1.5625rem;
            height: auto;
        }

        .header__top {
            padding: 1.25rem 1.25rem;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            border-bottom: 0.5px solid #BCA13480;
            align-items: center;
            max-width: 1140px;
            width: 100%;
            margin: 0 auto;
        }

        .header__bottom {
            padding: 0 1.25rem;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            height: 5.3125rem;
            align-items: center;
            z-index: 9;
            max-width: 1140px;
            width: 100%;
            margin: 0 auto;
        }

        .header__inner {
            display: flex;
            flex-direction: row;
            gap: 3.75rem;
        }

        .header__inner[hidden] {
            display: none;
        }

        .header__item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .header__phone {
            font-weight: 500;
            font-size: 1.25rem;
            line-height: 105%;
            letter-spacing: -0.02em;
            color: #FFFFFF;
            text-decoration: none;
        }

        .header__phone:hover {
            color: #C9AC37;
        }

        .header__feedback {
            font-weight: 500;
            font-size: 1rem;
            line-height: 105%;
            letter-spacing: -0.02em;
            text-decoration: underline dotted;
            color: #C9AC37;
            background-color: transparent;
            border: none;
            padding: 0;
            margin: 0;
            text-align: left;
            cursor: pointer;
        }

        .header__feedback:hover {
            color: #C9AC37;
        }

        .header__working_hours {
            font-weight: 400;
            font-size: 1rem;
            line-height: 105%;
            letter-spacing: -0.02em;
            color: #AAAAAA;
            margin-bottom: 0;
            margin-top: 0;
        }

        /* Search placeholder */
        .search-placeholder {
            width: 100%;
            padding: 0.625rem;
            background: #2A2A2A;
            border: 1px solid #BCA13480;
            border-radius: 0.5rem;
            color: white;
            font-size: 1rem;
        }

        .search-placeholder::placeholder {
            color: #666;
        }

        @media screen and (min-width: 768px) {
            .header_search_open .header__top {
                justify-content: unset;
                gap: 9.4375rem;
            }

            .header._mobile {
                display: none;
            }
        }

        /* Mobile styles */
        @media screen and (max-width: 767px) {

            .menu {
                display: flex;
                min-width: max-content;
                background: #141313;
                color: #fff;
                /* gap: 1.5rem; */
                gap: 0.9375rem;
            }

            .site_nav_mobile .menu {
                flex-direction: column;
            }

            .menu-submenu {
                display: none;
            }

            .menu__btn {
                padding: 0.9375rem 0;
                width: 100%;
                justify-content: space-between;
                background: transparent;
                font-size: 1.25rem;
            }

            .menu__btn[aria-expanded="true"] {
                border-radius: .5rem .5rem 0 0;
                background-color: #141313;
                border: none;
            }

            .menu__link {
                line-height: 120%;
                text-decoration: none;
                padding: 0.625rem 0 0.625rem 1.25rem;
                font-size: 1.125rem;
            }

            .menu__link:hover,
            .menu__btn:hover,
            .menu__btn[aria-expanded="true"],
            .menu__btn:hover svg,
            .menu__btn[aria-expanded="true"] svg,
            .menu__btn:hover path,
            .menu__btn[aria-expanded="true"] path {
                color: #C9AC37;
                stroke: #C9AC37;
            }

            .menu__btn-icon {
                color: inherit;
                width: .875rem;
                height: .875rem;
            }

            .menu__btn[aria-expanded="true"] .menu__btn-icon {
                transform: rotate(180deg);
            }

            .menu__item {
                position: relative;
            }

            #catalog-submenu {
                max-height: max-content;
                height: 100%;
            }

            #about-submenu {
                border-radius: 0 0 .5rem .5rem;
            }

            .menu[hidden] {
                display: none;
            }

            .menu.menu_submenu {
                flex-direction: column;
                gap: 0;
                overflow-y: auto;
                max-height: max-content;
                height: 100%;
                position: static;
                background: transparent;
                padding-left: 1rem;
            }

            .logo {
                width: max-content;
            }

            header .logo img {
                width: 9.0625rem;
                height: auto;
            }

            .text {
                font-weight: 400;
                font-size: 1.125rem;
            }

            .header._desktop {
                display: none;
            }

            .header._mobile {
                position: relative;
                padding: 1.015625rem 1.5rem;
                flex-direction: row;
                background-color: #141313;
                width: 100%;
                justify-content: space-between;
                align-items: center;
                display: flex;
            }

            .header__buttons {
                gap: 0.625rem;
            }

            .header__button {
                padding: 0.40625rem 0.4375rem;
                border-radius: 0.625rem;
                gap: 0.375rem;
                font-size: 1rem;
            }

            .header__account {
                padding: 0.40625rem 0.4375rem;
                border-radius: 0.625rem;
                gap: 0.375rem;
            }

            .header__account svg {
                width: 1.25rem;
            }

            .header__button svg {
                width: 1.25rem;
            }

            .burger {
                width: 4.875rem;
            }

            .burger__inner {
                display: flex;
                flex-direction: column;
                width: 1.25rem;
                height: 1.25rem;
                justify-content: space-between;
                padding: 0.125rem 0;
                cursor: pointer;
                border: none;
                background-color: #141313;
            }

            .burger__item {
                width: 1.25rem;
                height: 0.125rem;
                background: #FFFFFF;
                border: none;
                border-radius: 0.0625rem;
                transition: transform 0.3s ease;
            }

            .burger__inner._active .burger__item:nth-child(1) {
                transform: rotate(45deg) translateY(0.3125rem) translateX(0.3125rem);
            }

            .burger__inner._active .burger__item:nth-child(2) {
                display: none;
            }

            .burger__inner._active .burger__item:nth-child(3) {
                transform: rotate(-45deg) translateY(-0.3125rem) translateX(0.3125rem);
            }

            .burger__content {
                display: none;
                width: 100%;
                max-height: max-content;
                height: 100%;
                overflow-y: auto;
            }

            .burger__content._active {
                z-index: 5;
                position: absolute;
                display: flex;
                color: #AAAAAA;
                background-color: #141313;
                top: 4.125rem;
                left: 0;
                flex-direction: column;
                padding: 3.75rem 1.5rem;
                min-height: 100vh;
                gap: 3.75rem;
            }

            .burger__content._active input[type="search"] {
                width: 100% !important;
            }

            .site_nav_mobile .menu {
                flex-direction: column;
                width: 100%;
            }
        }
    </style>

</head>

<body class="hold-transition sidebar-mini layout-fixed admin-layout {{ auth()->user()?->isManagerTruck() ? '' : 'admin-no-sidebar' }}">
    @php
        $front = rtrim(config('app.frontend_url', 'https://truck-import.ru'), '/');
        $lk = rtrim(config('app.url', 'http://localhost'), '/');
        $isTruckManager = auth()->user()?->isManagerTruck() ?? false;
    @endphp
@unless($isTruckManager)
<ul class="top-nav__right">
            <li class="top-nav__item top-nav__item--dropdown dropdown">
                <a class="top-nav__link" data-toggle="dropdown" href="#">
                    <i class="far fa-comments"></i>
                    <span class="top-nav__badge">3</span>
                </a>
                <div class="top-nav__dropdown dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">Call me whenever you can...</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="" alt="User Avatar" class="img-size-50 img-circle mr-3">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">I got your message bro</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="" alt="User Avatar" class="img-size-50 img-circle mr-3">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">The subject goes here</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                </div>
            </li>
            <li class="top-nav__item top-nav__item--dropdown dropdown">
                <a class="top-nav__link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="top-nav__badge top-nav__badge--warning">15</span>
                </a>
                <div class="top-nav__dropdown dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">15 Notifications</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-envelope mr-2"></i> 4 new messages
                        <span class="float-right text-muted text-sm">3 mins</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-users mr-2"></i> 8 friend requests
                        <span class="float-right text-muted text-sm">12 hours</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-file mr-2"></i> 3 new reports
                        <span class="float-right text-muted text-sm">2 days</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                </div>
            </li>
            <li class="top-nav__item">
                <span class="top-nav__user">
                    <i class="top-nav__icon fas fa-user-circle"></i>
                    <span class="top-nav__user-name">{{ auth()->user()->full_name ?? auth()->user()->name }}</span>
                </span>
                <span class="top-nav__role">{{ auth()->user()->role?->label() }}</span>
            </li>
            <li class="top-nav__item">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="top-nav__btn top-nav__btn--danger">
                        <i class="fas fa-sign-out-alt"></i><span class="top-nav__btn-text">Выйти</span>
                    </button>
                </form>
            </li>
            <li class="top-nav__item">
                <a class="top-nav__link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
@endunless
    @include('partials.site-header', ['accountUrl' => $isTruckManager ? route('manager.profile') : $lk])

    @if($isTruckManager)
    <nav class="d-lg-none border-bottom bg-white px-2 py-2" aria-label="Разделы кабинета менеджера">
        <ul class="nav nav-pills flex-nowrap" style="overflow-x:auto">
            <li class="nav-item flex-shrink-0"><a class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}" href="{{ route('manager.dashboard') }}">Дашборд</a></li>
            <li class="nav-item flex-shrink-0"><a class="nav-link {{ request()->routeIs('manager.search') ? 'active' : '' }}" href="{{ route('manager.search') }}">Поиск</a></li>
            <li class="nav-item flex-shrink-0"><a class="nav-link {{ request()->routeIs('manager.orders.*') ? 'active' : '' }}" href="{{ route('manager.orders.index') }}">Заказы</a></li>
            <li class="nav-item flex-shrink-0"><a class="nav-link {{ request()->routeIs('manager.invoices.*') ? 'active' : '' }}" href="{{ route('manager.invoices.index') }}">Счета</a></li>
            <li class="nav-item flex-shrink-0"><a class="nav-link {{ request()->routeIs('manager.companies.*') ? 'active' : '' }}" href="{{ route('manager.companies.index') }}">Компании</a></li>
            <li class="nav-item flex-shrink-0"><a class="nav-link {{ request()->routeIs('manager.profile*') ? 'active' : '' }}" href="{{ route('manager.profile') }}">Профиль</a></li>
            <li class="nav-item flex-shrink-0">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="nav-link">Выйти</button>
                </form>
            </li>
        </ul>
    </nav>
    @endif

    <div class="wrapper">


        



        <!-- Main Sidebar Container -->
        @if($isTruckManager)
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('manager.dashboard') }}" class="brand-link">
                <span class="brand-text font-weight-light">Truck Import</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                        <li class="nav-item">
                            <a href="{{ route('manager.dashboard') }}" class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i><p>Дашборд</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('manager.search') }}" class="nav-link {{ request()->routeIs('manager.search') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-search"></i><p>Поиск деталей</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('manager.orders.index') }}" class="nav-link {{ request()->routeIs('manager.orders.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-list"></i><p>Заказы</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('manager.invoices.index') }}" class="nav-link {{ request()->routeIs('manager.invoices.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice"></i><p>Счета на оплату</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('manager.companies.index') }}" class="nav-link {{ request()->routeIs('manager.companies.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-building"></i><p>Компании</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('manager.profile') }}" class="nav-link {{ request()->routeIs('manager.profile*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user"></i><p>Мой профиль</p>
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="nav-link border-0 bg-transparent text-left w-100">
                                    <i class="nav-icon fas fa-sign-out-alt"></i><p>Выйти</p>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        @else
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('admin.index') }}" class="brand-link">
                <span class="brand-text font-weight-light">Truck Import</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        {{-- Заказы и Счета --}}
                        <li class="nav-header">Заказы</li>
                        <li class="nav-item">
                            <a href="{{ route('admin.orders.index') }}"
                                class="nav-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Заказы</p>
                            </a>
                        </li>
                        @if(auth()->user()->canCreateInvoice())
                        <li class="nav-item">
                            <a href="{{ route('admin.invoices.index') }}"
                                class="nav-link {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                <p>Счета</p>
                            </a>
                        </li>
                        @else
                        <li class="nav-item">
                            <a href="{{ route('admin.invoices.index') }}"
                                class="nav-link {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice"></i>
                                <p>Счета</p>
                            </a>
                        </li>
                        @endif

                        {{-- Пользователи (admin_truck и admin_customer) --}}
                        @if(auth()->user()->canManageUsers() || auth()->user()->isAdminCustomer())
                        <li class="nav-header">Пользователи</li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Пользователи</p>
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->canManageUsers())
                        <li class="nav-item">
                            <a href="{{ route('admin.truck-managers.index') }}"
                               class="nav-link {{ request()->routeIs('admin.truck-managers*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-tie"></i>
                                <p>Менеджеры</p>
                            </a>
                        </li>
                        {{-- Каталог --}}
                        <li class="nav-header">Управление магазином</li>
                        <li class="nav-item">
                            <a href="{{ route('admin.brands.index') }}"
                                class="nav-link {{ request()->routeIs('admin.brands*') ? 'active' : '' }}">
                                <i class="nav-icon fab fa-bandcamp"></i>
                                <p>Бренды</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.truck-manufacturers.index') }}"
                                class="nav-link {{ request()->routeIs('admin.truck-manufacturers*') ? 'active' : '' }}">
                                <i class="nav-icon fab fa-sourcetree"></i>
                                <p>Производители Траков</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.truck-models.index') }}"
                                class="nav-link {{ request()->routeIs('admin.truck-models*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-truck"></i>
                                <p>Модели Траков</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.part-types.index') }}"
                                class="nav-link {{ request()->routeIs('admin.part-types*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-wrench"></i>
                                <p>Типы запчастей</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.products.index') }}"
                                class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Товары</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ Route::has('admin.posts.index') ? route('admin.posts.index') : url('/admin/posts') }}"
                                class="nav-link {{ request()->routeIs('admin.posts*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-newspaper"></i>
                                <p>Блог / Статьи</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.pages.index') }}"
                                class="nav-link {{ request()->routeIs('admin.pages*') && !request()->routeIs('admin.pages.edit', 'site_settings') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Страницы</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.pages.edit', 'site_settings') }}"
                                class="nav-link {{ request()->routeIs('admin.pages.edit') && request()->route('page')->slug === 'site_settings' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Настройки сайта</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/horizon" target="_blank">
                                <i class="fas fa-tasks"></i>
                                <span>Мониторинг Очередей</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>
        @endif

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('manager-alerts')
            @yield('content')
        </div>
        <!-- /.content-wrapper -->

        @include('partials.site-footer')

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src=" {{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src=" {{ asset('AdminLTE/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src=" {{ asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src=" {{ asset('AdminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src=" {{ asset('AdminLTE/dist/js/adminlte.js') }}"></script>
    <!-- bs-custom-file-input -->
    <script src="{{ asset('AdminLTE/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('AdminLTE/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('AdminLTE/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        $(function() {
            bsCustomFileInput.init();

            //Initialize Select2 Elements
            // $('.categories_multiple').select2()
        });
    </script>
    @stack('scripts')
</body>

</html>
