@php
    $user = auth()->user();

    if ($user?->isManagerTruck()) {
        $items = [
            ['label' => 'Поиск деталей', 'route' => 'manager.search', 'active' => 'manager.search'],
            ['label' => 'Заказы', 'route' => 'manager.orders.index', 'active' => 'manager.orders.*'],
            ['label' => 'Счета на оплату', 'route' => 'manager.invoices.index', 'active' => 'manager.invoices.*'],
            ['label' => 'Компании', 'route' => 'manager.companies.index', 'active' => 'manager.companies.*'],
            ['label' => 'Мой профиль', 'route' => 'manager.profile', 'active' => 'manager.profile*'],
        ];
    } elseif ($user?->isAdminTruck()) {
        $items = [
            ['label' => 'Дашборд', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard*'],
            ['label' => 'Поиск деталей', 'route' => 'admin.search.index', 'active' => 'admin.search.*'],
            ['label' => 'Заказы', 'route' => 'admin.orders.index', 'active' => 'admin.orders*'],
            ['label' => 'Счета на оплату', 'route' => 'admin.invoices.index', 'active' => 'admin.invoices*'],
            ['label' => 'Мой профиль', 'route' => 'admin.profile', 'active' => 'admin.profile*'],
            ['label' => 'Профили компаний', 'route' => 'admin.users.index', 'active' => ['admin.users*', 'admin.companies*']],
            ['label' => 'Менеджеры', 'route' => 'admin.truck-managers.index', 'active' => 'admin.truck-managers*'],
            ['label' => 'Блог / Статьи', 'route' => 'admin.posts.index', 'active' => 'admin.posts*'],
            ['label' => 'Страницы', 'route' => 'admin.pages.index', 'active' => 'admin.pages.index'],
            ['label' => 'Настройки сайта', 'route' => 'admin.pages.edit', 'parameters' => 'site_settings', 'active_site_settings' => true],
            ['label' => 'Товары', 'route' => 'admin.products.index', 'active' => ['admin.products.index', 'admin.products.create', 'admin.products.edit']],
        ];
    } elseif ($user?->isAdminCustomer()) {
        $items = [
            ['label' => 'Поиск деталей', 'route' => 'customer.search.index', 'active' => 'customer.search*'],
            ['label' => 'Заказы', 'route' => 'customer.orders.index', 'active' => 'customer.orders*'],
            ['label' => 'Счета на оплату', 'route' => 'customer.invoices.index', 'active' => 'customer.invoices*'],
            ['label' => 'Мой профиль', 'route' => 'customer.profile', 'active' => 'customer.profile*'],
            ['label' => 'Профиль компании', 'route' => 'customer.company.show', 'active' => 'customer.company*'],
        ];
    } else {
        $items = [
            ['label' => 'Поиск деталей', 'route' => 'customer.search.index', 'active' => 'customer.search*'],
            ['label' => 'Заказы', 'route' => 'customer.orders.index', 'active' => 'customer.orders*'],
            ['label' => 'Счета на оплату', 'route' => 'customer.invoices.index', 'active' => 'customer.invoices*'],
            ['label' => 'Мой профиль', 'route' => 'customer.profile', 'active' => 'customer.profile*'],
        ];
    }
@endphp

<ul class="cabinet-navigation__list">
    @foreach ($items as $item)
        @php
            $activePatterns = (array) ($item['active'] ?? $item['route']);
            $isActive = request()->routeIs(...$activePatterns);
            if (!empty($item['active_site_settings'])) {
                $currentPage = request()->route('page');
                $isActive = request()->routeIs('admin.pages.edit') && data_get($currentPage, 'slug') === 'site_settings';
            }
        @endphp
        <li class="cabinet-navigation__item">
            <a
                href="{{ $item['url'] ?? route($item['route'], $item['parameters'] ?? []) }}"
                class="cabinet-navigation__link {{ $isActive ? 'is-active' : '' }}"
                @if ($isActive) aria-current="page" @endif
            >{{ $item['label'] }}</a>
        </li>
    @endforeach
</ul>
