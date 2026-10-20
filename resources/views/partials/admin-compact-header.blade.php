@php
    $front = rtrim(config('app.frontend_url', 'https://truck-import.ru'), '/');
@endphp

<header class="admin-compact-header">
    <div class="admin-compact-header__inner">
        <a class="admin-compact-header__logo" href="{{ $front }}/" aria-label="Truck Import — главная">
            <img src="/logo.svg" alt="TRUCK IMPORT" width="500" height="300">
        </a>

        <div class="admin-compact-header__actions">
            <div class="admin-compact-header__user">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                <span>{{ auth()->user()->full_name ?? auth()->user()->name }}</span>
                <small>{{ auth()->user()->role?->label() }}</small>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button class="admin-compact-header__logout" type="submit">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Выйти</span>
                </button>
            </form>
        </div>
    </div>
</header>
