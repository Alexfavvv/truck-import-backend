@php
    $compactFront = rtrim(config('app.frontend_url', 'https://truck-import.ru'), '/');
    $compactUser = auth()->user();
    $compactName = $compactUser?->full_name ?? $compactUser?->name ?? '';
    $compactRoleLabel = $compactUser?->role?->label();
    $showCompactRole = filled($compactRoleLabel) && trim($compactRoleLabel) !== trim($compactName);
@endphp

<header class="cabinet-compact-header">
    <div class="cabinet-compact-header__inner">
        <a class="cabinet-compact-header__logo" href="{{ $compactFront }}/" aria-label="Truck Import — главная">
            <img src="/logo.svg" alt="TRUCK IMPORT" width="500" height="300">
        </a>

        <div class="cabinet-compact-header__actions">
            <div class="cabinet-compact-header__user">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                <span>{{ $compactName }}</span>
                @if($showCompactRole)
                    <small>{{ $compactRoleLabel }}</small>
                @endif
            </div>

            <form action="{{ $logoutUrl }}" method="POST">
                @csrf
                <button class="cabinet-compact-header__logout" type="submit">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Выйти</span>
                </button>
            </form>

            @if(!empty($cartUrl))
                <a class="cabinet-compact-header__cart" href="{{ $cartUrl }}">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    <span>Корзина</span>
                </a>
            @endif
        </div>
    </div>
</header>
