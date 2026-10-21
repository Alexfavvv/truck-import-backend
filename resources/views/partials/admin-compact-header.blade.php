@php
    $front = rtrim(config('app.frontend_url', 'https://truck-import.ru'), '/');
    $currentUser = auth()->user();
    $displayName = $currentUser->full_name ?? $currentUser->name;
    $roleLabel = $currentUser->role?->label();
    $showRole = filled($roleLabel) && trim($roleLabel) !== trim($displayName);
    $adminCartCount = \App\Models\CartItem::query()
        ->whereHas('cart', fn ($query) => $query->where('user_id', $currentUser->id))
        ->sum('quantity');
@endphp

<header class="admin-compact-header">
    <div class="admin-compact-header__inner">
        <a class="admin-compact-header__logo" href="{{ $front }}/" aria-label="Truck Import — главная">
            <img src="/logo.svg" alt="TRUCK IMPORT" width="500" height="300">
        </a>

        <div class="admin-compact-header__actions">
            <div class="admin-compact-header__user">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                <span>{{ $displayName }}</span>
                @if($showRole)
                    <small>{{ $roleLabel }}</small>
                @endif
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button class="admin-compact-header__logout" type="submit">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Выйти</span>
                </button>
            </form>
            <a class="admin-compact-header__cart" href="{{ route('admin.cart.index') }}">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                <span>Корзина</span>
                @if($adminCartCount > 0)
                    <span class="admin-compact-header__cart-count" aria-label="Товаров: {{ $adminCartCount }}">{{ $adminCartCount }}</span>
                @endif
            </a>
        </div>
    </div>
</header>
