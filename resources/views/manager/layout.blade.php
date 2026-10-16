<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Кабинет менеджера') — Truck Import</title>
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE/dist/css/adminlte.min.css') }}">
    <style>
        body { background:#f5f5f3; color:#252525; }
        .manager-header { background:#141313; color:#fff; border-bottom:3px solid #c9ac37; }
        .manager-header-inner { max-width:1180px; margin:auto; padding:16px 20px; display:flex; flex-wrap:wrap; gap:16px; align-items:center; justify-content:space-between; }
        .manager-brand { color:#c9ac37; font-size:1.35rem; font-weight:700; text-decoration:none; }
        .manager-brand:hover { color:#ead988; }
        .manager-nav { display:flex; flex-wrap:wrap; gap:6px; }
        .manager-nav a { color:#fff; padding:8px 12px; border-radius:6px; }
        .manager-nav a:hover,.manager-nav a[aria-current=page] { background:#34312a; color:#c9ac37; }
        .manager-content { max-width:1180px; margin:28px auto; padding:0 20px 40px; }
        .manager-card { background:#fff; border-radius:10px; box-shadow:0 3px 18px #0000000d; padding:24px; margin-bottom:20px; }
        .manager-card h1 { font-size:1.8rem; margin-bottom:20px; }
        .manager-actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
        .manager-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px; }
        .manager-stat { border-left:4px solid #c9ac37; padding:18px; background:#fff; border-radius:6px; }
        .manager-stat strong { display:block; font-size:2rem; }
        .manager-empty { padding:32px; text-align:center; color:#666; }
        .manager-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px; }
        .manager-form-grid label { display:block; font-weight:600; }
        .manager-form-grid input,.manager-form-grid select { margin-top:6px; }
        .btn-primary { background:#b99a2e; border-color:#b99a2e; color:#171717; }
        .btn-primary:hover { background:#a48621; border-color:#a48621; color:#171717; }
        @media(max-width:700px) { .manager-header-inner { align-items:flex-start; } .manager-nav { width:100%; } .manager-nav a { padding:7px 8px; } .manager-card { padding:16px; } }
    </style>
</head>
<body>
<header class="manager-header"><div class="manager-header-inner">
    <a class="manager-brand" href="{{ route('manager.dashboard') }}">TRUCK IMPORT <small>· Менеджер</small></a>
    <nav class="manager-nav" aria-label="Кабинет менеджера">
        <a href="{{ route('manager.dashboard') }}" @if(request()->routeIs('manager.dashboard')) aria-current="page" @endif>Главная</a>
        <a href="{{ route('manager.search') }}" @if(request()->routeIs('manager.search')) aria-current="page" @endif>Поиск товаров</a>
        <a href="{{ route('manager.orders.index') }}" @if(request()->routeIs('manager.orders.*')) aria-current="page" @endif>Заказы</a>
        <a href="{{ route('manager.invoices.index') }}" @if(request()->routeIs('manager.invoices.*')) aria-current="page" @endif>Счета</a>
        <a href="{{ route('manager.companies.index') }}" @if(request()->routeIs('manager.companies.*')) aria-current="page" @endif>Компании</a>
        <a href="{{ route('manager.profile') }}" @if(request()->routeIs('manager.profile*')) aria-current="page" @endif>Мой профиль</a>
    </nav>
    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-sm btn-outline-light" type="submit">Выйти</button></form>
</div></header>
<main class="manager-content">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<script>document.querySelectorAll('[data-submit-loading]').forEach(form => form.addEventListener('submit', () => { const button = form.querySelector('[type=submit]'); if(button) { button.disabled = true; button.textContent = 'Подождите…'; } }));</script>
</body>
</html>
