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
<body class="hold-transition sidebar-mini layout-fixed customer-no-sidebar">
    @php
        $front = rtrim(config('app.frontend_url', 'https://truck-import.ru'), '/');
        $accountUrl = auth()->user()?->cabinetUrl() ?? route('login');
    @endphp

<header class="header _desktop">
        <div class="header__top">
            <a class="logo" href="{{ $front }}">
                <img src="/logo.svg"
                    alt="logo"
                    width="500"
                    height="300">
            </a>

            <div class="header__inner">
                <div class="header__item">
                    <a class="header__phone" href="tel:+74957403306">+7 (495) 740-33-06</a>
                    <button class="header__feedback" onclick="document.getElementById('header__modal').showModal()">
                        Заказать звонок
                    </button>
                </div>

                <div class="header__item">
                    <a class="header__phone" href="tel:+79099131186">+7 (909) 913-11-86</a>
                    <p class="header__working_hours">Пн-Пт: 9:00 - 18:00</p>
                </div>
            </div>
        </div>

        <div class="header__bottom">
            <nav class="site_nav" aria-label="Сайт">
                <ul class="menu main__menu">
                    <li class="menu__item" data-has-children>
                        <button class="menu__btn" onclick="toggleSubmenu(this, 'catalog-submenu')" aria-expanded="false" aria-controls="catalog-submenu">
                            Каталог запчастей
                            <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                                <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <ul class="menu menu_submenu" id="catalog-submenu" hidden>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/mercedes">Mercedes</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/man">Man</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/scania">Scania</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/volvo">Volvo</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/hengst">Hengst</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/kolbenschmidt">Kolbenschmidt</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/daf">Daf</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/iveco">Iveco</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/renault">Renault</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/valeo">Valeo</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/john-deere">John Deere</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/komatsu">Komatsu</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/iveco-daily">Iveco Daily</a></li>
                        </ul>
                    </li>
                    <li class="menu__item" data-has-children>
                        <button class="menu__btn" onclick="toggleSubmenu(this, 'buyers-submenu')" aria-expanded="false" aria-controls="buyers-submenu">
                            Покупателям
                            <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                                <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <ul class="menu menu_submenu" id="buyers-submenu" hidden>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/oplata-i-dostavka">Оплата и доставка</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/garantii-i-vozvrat">Гарантия и возврат</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/contacts">Контакты</a></li>
                        </ul>
                    </li>
                    <li class="menu__item" data-has-children>
                        <button class="menu__btn" onclick="toggleSubmenu(this, 'about-submenu')" aria-expanded="false" aria-controls="about-submenu">
                            О проекте
                            <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                                <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <ul class="menu menu_submenu" id="about-submenu" hidden>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/vakansii">Вакансии</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <div class="header__buttons">
                <a class="header__account" aria-label="Аккаунт" href="{{ $accountUrl }}">
                    <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.85938 10.2855C5.85938 13.9379 8.84766 16.9261 12.5 16.9261C16.1523 16.9261 19.1406 13.9379 19.1406 10.2855C19.1406 6.63318 16.1523 3.6449 12.5 3.6449C8.84766 3.6449 5.85938 6.63318 5.85938 10.2855ZM17.5781 10.2855C17.5781 13.0785 15.293 15.3636 12.5 15.3636C9.70703 15.3636 7.42188 13.0785 7.42188 10.2855C7.42188 7.49255 9.70703 5.2074 12.5 5.2074C15.293 5.2074 17.5781 7.49255 17.5781 10.2855Z" fill="#C9AC37" />
                        <path d="M4.76562 22.5251C6.83594 20.4548 9.57031 19.322 12.5 19.322C15.4297 19.322 18.1641 20.4548 20.2344 22.5251L21.3477 21.4118C18.9844 19.0681 15.8398 17.7595 12.5 17.7595C9.16016 17.7595 6.01563 19.0681 3.65234 21.4118L4.76562 22.5251Z" fill="#C9AC37" />
                    </svg>
                </a>

                <a class="header__button" aria-label="Корзина" href="{{ $front }}/cart">
                    <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_199_680)">
                            <path d="M24.7961 7.29121C24.6508 7.08496 24.414 6.96152 24.1617 6.96152H7.58124L6.32577 2.6334C5.83358 0.928711 4.66405 0.745117 4.18436 0.745117H0.837488C0.408582 0.745117 0.0617065 1.09277 0.0617065 1.5209C0.0617065 1.94902 0.409363 2.29668 0.837488 2.29668H4.18358C4.28983 2.29668 4.61249 2.29668 4.8328 3.05762L9.15077 18.9256C9.24452 19.26 9.54999 19.4912 9.89764 19.4912H20.4437C20.7711 19.4912 21.0633 19.2865 21.1734 18.9779L24.8906 7.99902C24.9766 7.76152 24.9406 7.49668 24.7953 7.29043L24.7961 7.29121ZM19.8984 17.9404H10.4875L8.01796 8.51387H23.0594L19.8984 17.9404ZM18.3594 21.0639C17.2805 21.0639 16.4062 21.9381 16.4062 23.017C16.4062 24.0959 17.2805 24.9701 18.3594 24.9701C19.4383 24.9701 20.3125 24.0959 20.3125 23.017C20.3125 21.9381 19.4383 21.0639 18.3594 21.0639ZM11.3281 21.0639C10.2492 21.0639 9.37499 21.9381 9.37499 23.017C9.37499 24.0959 10.2492 24.9701 11.3281 24.9701C12.407 24.9701 13.2812 24.0959 13.2812 23.017C13.2812 21.9381 12.407 21.0639 11.3281 21.0639Z" fill="black" />
                        </g>
                        <defs>
                            <clipPath id="clip0_199_680">
                                <rect width="25" height="25" fill="white" transform="translate(0 0.357605)" />
                            </clipPath>
                        </defs>
                    </svg>
                    Корзина
                </a>
            </div>
        </div>
    </header>

    <!-- Mobile Header -->
    <header class="header _mobile">
        <div class="burger">
            <button class="burger__inner" onclick="toggleBurger()" aria-label="Меню">
                <span class="burger__item"></span>
                <span class="burger__item"></span>
                <span class="burger__item"></span>
            </button>
        </div>

        <a class="logo" href="{{ $front }}">
            <img src="/logo.svg"
                alt="logo"
                width="500"
                height="300">
        </a>

        <div class="header__buttons">
            <a class="header__account" aria-label="Аккаунт" href="{{ $accountUrl }}">
                <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.85938 10.2855C5.85938 13.9379 8.84766 16.9261 12.5 16.9261C16.1523 16.9261 19.1406 13.9379 19.1406 10.2855C19.1406 6.63318 16.1523 3.6449 12.5 3.6449C8.84766 3.6449 5.85938 6.63318 5.85938 10.2855ZM17.5781 10.2855C17.5781 13.0785 15.293 15.3636 12.5 15.3636C9.70703 15.3636 7.42188 13.0785 7.42188 10.2855C7.42188 7.49255 9.70703 5.2074 12.5 5.2074C15.293 5.2074 17.5781 7.49255 17.5781 10.2855Z" fill="#C9AC37" />
                    <path d="M4.76562 22.5251C6.83594 20.4548 9.57031 19.322 12.5 19.322C15.4297 19.322 18.1641 20.4548 20.2344 22.5251L21.3477 21.4118C18.9844 19.0681 15.8398 17.7595 12.5 17.7595C9.16016 17.7595 6.01563 19.0681 3.65234 21.4118L4.76562 22.5251Z" fill="#C9AC37" />
                </svg>
            </a>

            <a class="header__button" aria-label="Корзина" href="{{ $front }}/cart">
                <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_199_680)">
                        <path d="M24.7961 7.29121C24.6508 7.08496 24.414 6.96152 24.1617 6.96152H7.58124L6.32577 2.6334C5.83358 0.928711 4.66405 0.745117 4.18436 0.745117H0.837488C0.408582 0.745117 0.0617065 1.09277 0.0617065 1.5209C0.0617065 1.94902 0.409363 2.29668 0.837488 2.29668H4.18358C4.28983 2.29668 4.61249 2.29668 4.8328 3.05762L9.15077 18.9256C9.24452 19.26 9.54999 19.4912 9.89764 19.4912H20.4437C20.7711 19.4912 21.0633 19.2865 21.1734 18.9779L24.8906 7.99902C24.9766 7.76152 24.9406 7.49668 24.7953 7.29043L24.7961 7.29121ZM19.8984 17.9404H10.4875L8.01796 8.51387H23.0594L19.8984 17.9404ZM18.3594 21.0639C17.2805 21.0639 16.4062 21.9381 16.4062 23.017C16.4062 24.0959 17.2805 24.9701 18.3594 24.9701C19.4383 24.9701 20.3125 24.0959 20.3125 23.017C20.3125 21.9381 19.4383 21.0639 18.3594 21.0639ZM11.3281 21.0639C10.2492 21.0639 9.37499 21.9381 9.37499 23.017C9.37499 24.0959 10.2492 24.9701 11.3281 24.9701C12.407 24.9701 13.2812 24.0959 13.2812 23.017C13.2812 21.9381 12.407 21.0639 11.3281 21.0639Z" fill="black" />
                    </g>
                    <defs>
                        <clipPath id="clip0_199_680">
                            <rect width="25" height="25" fill="white" transform="translate(0 0.357605)" />
                        </clipPath>
                    </defs>
                </svg>
            </a>
        </div>

        <div class="burger__content" id="burgerContent">
            <nav class="site_nav_mobile" aria-label="Сайт">
                <ul class="menu main__menu">
                    <li class="menu__item" data-has-children>
                        <button class="menu__btn" onclick="toggleMobileSubmenu(this, 'mobile-catalog-submenu')" aria-expanded="false">
                            Каталог запчастей
                            <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                                <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <ul class="menu menu_submenu" id="mobile-catalog-submenu" hidden>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/mercedes">Mercedes</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/man">Man</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/brands/scania">Scania</a></li>
                        </ul>
                    </li>
                    <li class="menu__item" data-has-children>
                        <button class="menu__btn" onclick="toggleMobileSubmenu(this, 'mobile-buyers-submenu')" aria-expanded="false">
                            Покупателям
                            <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                                <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <ul class="menu menu_submenu" id="mobile-buyers-submenu" hidden>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/oplata-i-dostavka">Оплата и доставка</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/garantii-i-vozvrat">Гарантия и возврат</a></li>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/contacts">Контакты</a></li>
                        </ul>
                    </li>
                    <li class="menu__item" data-has-children>
                        <button class="menu__btn" onclick="toggleMobileSubmenu(this, 'mobile-about-submenu')" aria-expanded="false">
                            О проекте
                            <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                                <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <ul class="menu menu_submenu" id="mobile-about-submenu" hidden>
                            <li class="menu__item"><a class="menu__link" href="{{ $front }}/vakansii">Вакансии</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Modal -->
    <dialog class="header__modal" id="header__modal">
        <div class="feedback">
            <div style="padding: 3rem; color: white;">
                <h3 style="font-size: 2.4rem; margin-bottom: 2rem; color: #C9AC37;">Заказать звонок</h3>
                <form style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <input type="text" placeholder="Имя" style="width: 100%; padding: 1.2rem; background: #2A2A2A; border: 1px solid #BCA13480; border-radius: .5rem; color: white; font-size: 1.6rem;">
                    <input type="tel" placeholder="Телефон" style="width: 100%; padding: 1.2rem; background: #2A2A2A; border: 1px solid #BCA13480; border-radius: .5rem; color: white; font-size: 1.6rem;">
                    <button style="background: #C9AC37; color: #141313; padding: 1.2rem; border: none; border-radius: .5rem; font-size: 1.8rem; font-weight: 600; cursor: pointer; margin-top: 1rem;">Отправить</button>
                </form>
            </div>
        </div>
        <div class="header__modal_close" onclick="document.getElementById('header__modal').close()">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L10 10M10 10L19 19M10 10L1 19M10 10L19 1" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
    </dialog>

    <script>
        function toggleSubmenu(button, submenuId) {
            const submenu = document.getElementById(submenuId);
            const isOpen = !submenu.hidden;

            // Close all submenus first
            document.querySelectorAll('.menu_submenu').forEach(menu => {
                menu.hidden = true;
            });

            document.querySelectorAll('.menu__btn[aria-expanded="true"]').forEach(btn => {
                btn.setAttribute('aria-expanded', 'false');
            });

            // Open current if it was closed
            if (!isOpen) {
                submenu.hidden = false;
                button.setAttribute('aria-expanded', 'true');
            }
        }

        function toggleMobileSubmenu(button, submenuId) {
            const submenu = document.getElementById(submenuId);
            const isOpen = !submenu.hidden;

            if (isOpen) {
                submenu.hidden = true;
                button.setAttribute('aria-expanded', 'false');
            } else {
                submenu.hidden = false;
                button.setAttribute('aria-expanded', 'true');
            }
        }

        function toggleBurger() {
            const burgerBtn = document.querySelector('.burger__inner');
            const burgerContent = document.getElementById('burgerContent');
            const isActive = burgerBtn.classList.contains('_active');

            if (isActive) {
                burgerBtn.classList.remove('_active');
                burgerContent.classList.remove('_active');
                document.body.classList.remove('stop-scrolling');
            } else {
                burgerBtn.classList.add('_active');
                burgerContent.classList.add('_active');
                document.body.classList.add('stop-scrolling');
            }
        }

        // Close modal on backdrop click
        const modal = document.getElementById('header__modal');
        modal.addEventListener('click', (e) => {
            const rect = modal.getBoundingClientRect();
            const isClickOutside = e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom;
            if (isClickOutside) {
                modal.close();
            }
        });

        // Close mobile menu on link click
        document.querySelectorAll('.burger__content a').forEach(link => {
            link.addEventListener('click', () => {
                const burgerBtn = document.querySelector('.burger__inner');
                const burgerContent = document.getElementById('burgerContent');
                if (burgerBtn.classList.contains('_active')) {
                    burgerBtn.classList.remove('_active');
                    burgerContent.classList.remove('_active');
                    document.body.classList.remove('stop-scrolling');
                }
            });
        });
    </script>
    
<div class="wrapper">

    {{-- Navbar (BEM: top-nav) --}}
    <nav class="main-header top-nav">
        <ul class="top-nav__tabs">
            <li class="top-nav__item">
                <a href="{{ route('customer.search.index') }}"
                   class="top-nav__link {{ request()->routeIs('customer.search*') ? 'top-nav__link--active' : '' }}">
                    <i class="top-nav__icon fas fa-search"></i><span class="top-nav__text">Поиск деталей</span>
                </a>
            </li>
            <li class="top-nav__item">
                <a href="{{ route('customer.orders.index') }}"
                   class="top-nav__link {{ request()->routeIs('customer.orders*') ? 'top-nav__link--active' : '' }}">
                    <i class="top-nav__icon fas fa-clipboard-list"></i><span class="top-nav__text">Заказы</span>
                </a>
            </li>
            <li class="top-nav__item">
                <a href="{{ route('customer.invoices.index') }}"
                   class="top-nav__link {{ request()->routeIs('customer.invoices*') ? 'top-nav__link--active' : '' }}">
                    <i class="top-nav__icon fas fa-file-invoice-dollar"></i><span class="top-nav__text">Счета на оплату</span>
                </a>
            </li>
            <li class="top-nav__item">
                <a href="{{ route('customer.profile') }}"
                   class="top-nav__link {{ request()->routeIs('customer.profile') ? 'top-nav__link--active' : '' }}">
                    <i class="top-nav__icon fas fa-user"></i><span class="top-nav__text">Мой профиль</span>
                </a>
            </li>
            @if(auth()->user()->isAdminCustomer())
            <li class="top-nav__item">
                <a href="{{ route('customer.company.show') }}"
                   class="top-nav__link {{ request()->routeIs('customer.company*') || request()->routeIs('customer.managers*') ? 'top-nav__link--active' : '' }}">
                    <i class="top-nav__icon fas fa-building"></i><span class="top-nav__text">Профиль компании</span>
                </a>
            </li>
            @endif
        </ul>

        <ul class="top-nav__right">
            <li class="top-nav__item">
                <span class="top-nav__user">
                    <i class="top-nav__icon fas fa-building"></i>
                    <span class="top-nav__user-name">{{ auth()->user()->company ?? (auth()->user()->full_name ?? auth()->user()->name) }}</span>
                </span>
                <span class="top-nav__role">{{ auth()->user()->role?->label() }}</span>
            </li>
            <li class="top-nav__item">
                <form action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="top-nav__btn top-nav__btn--danger">
                        <i class="fas fa-sign-out-alt"></i><span class="top-nav__btn-text">Выйти</span>
                    </button>
                </form>
            </li>
        </ul>
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
