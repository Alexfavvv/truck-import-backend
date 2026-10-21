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
<body class="hold-transition sidebar-mini layout-fixed customer-no-sidebar customer-cabinet cabinet-compact-view">
    @php
        $front = rtrim(config('app.frontend_url', 'https://truck-import.ru'), '/');
    @endphp

    @include('partials.cabinet-compact-header', ['logoutUrl' => route('customer.logout'), 'cartUrl' => $front.'/cart'])

<div class="wrapper">

    <nav class="cabinet-navigation" aria-label="Разделы личного кабинета">
        @include('partials.cabinet-navigation')
    </nav>

    {{-- Sidebar (оставляем в разметке, но скрываем стилями через .customer-no-sidebar) --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('customer.dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">Трак Импорт</span>
        </a>
        <div class="sidebar">
            {{-- Профиль и меню можно вернуть, убрав класс customer-no-sidebar на body --}}
        </div>
    </aside>

    {{-- Content --}}
    <div class="content-wrapper">
        @yield('content')
    </div>

    @include('partials.site-footer')

    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script src="{{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<script src="{{ asset('AdminLTE/dist/js/adminlte.js') }}"></script>
@stack('scripts')
</body>
</html>
