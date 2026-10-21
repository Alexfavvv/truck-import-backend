@extends('admin.layouts.main')

@section('content')
<section class="orders-page admin-compact-page admin-compact-page--cart">
    <header class="orders-page__section-header admin-compact-page__header">
        <h1 class="orders-page__title">Корзина</h1>
        @if($cart->items->isNotEmpty())
            <span class="admin-cart__total">Всего: {{ number_format($cart->total, 2, '.', ' ') }} ₽</span>
        @endif
    </header>

    @if(session('success'))
        <div class="orders-page__alert orders-page__alert--success" role="status">{{ session('success') }}</div>
    @endif

    @if($cart->items->isNotEmpty())
        <form class="admin-cart" id="adminCartOrderForm" action="{{ route('admin.search.create-order') }}" method="POST">
            @csrf
            <div class="orders-page__table-wrap">
                <table class="orders-page__table admin-cart__table">
                    <thead>
                        <tr>
                            <th class="orders-page__th admin-cart__select-cell">
                                <label class="custom-checkbox" aria-label="Выбрать все товары">
                                    <input type="checkbox" class="custom-checkbox__input" data-cart-select-all>
                                    <span class="custom-checkbox__box" aria-hidden="true"></span>
                                </label>
                            </th>
                            <th class="orders-page__th admin-cart__image-cell"></th>
                            <th class="orders-page__th">Артикул / Бренд</th>
                            <th class="orders-page__th admin-cart__product-cell">Наименование</th>
                            <th class="orders-page__th admin-cart__price-cell">Цена, руб.</th>
                            <th class="orders-page__th admin-cart__quantity-cell">Кол-во</th>
                            <th class="orders-page__th admin-cart__sum-cell">Сумма, руб.</th>
                            <th class="orders-page__th admin-cart__remove-cell"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart->items as $item)
                            @php($product = $item->product)
                            <tr class="orders-page__row admin-cart__row" data-cart-row data-price="{{ $product->price }}">
                                <td class="orders-page__td admin-cart__select-cell">
                                    <input type="checkbox" name="selected_items[]" value="{{ $product->id }}" data-cart-select aria-label="Выбрать {{ $product->title }}">
                                </td>
                                <td class="orders-page__td admin-cart__image-cell">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="" class="admin-cart__image" loading="lazy">
                                    @else
                                        <span class="admin-cart__image-placeholder"><i class="far fa-image" aria-hidden="true"></i></span>
                                    @endif
                                </td>
                                <td class="orders-page__td admin-cart__sku-cell">
                                    <strong>{{ $product->sku }}</strong>
                                    <small>{{ optional($product->brand)->name ?? '—' }}</small>
                                </td>
                                <td class="orders-page__td admin-cart__product-cell">{{ $product->title }}</td>
                                <td class="orders-page__td admin-cart__price-cell">{{ number_format($product->price, 2, '.', ' ') }} ₽</td>
                                <td class="orders-page__td admin-cart__quantity-cell">
                                    <input type="number" min="1" max="999" value="{{ $item->quantity }}"
                                           class="admin-cart__quantity" data-cart-quantity
                                           data-update-url="{{ route('admin.cart.items.update', $item) }}"
                                           aria-label="Количество: {{ $product->title }}">
                                </td>
                                <td class="orders-page__td admin-cart__sum-cell" data-cart-subtotal>
                                    {{ number_format($item->subtotal, 2, '.', ' ') }} ₽
                                </td>
                                <td class="orders-page__td admin-cart__remove-cell">
                                    <button type="button" class="admin-cart__remove" data-cart-remove
                                            data-remove-url="{{ route('admin.cart.items.destroy', $item) }}"
                                            aria-label="Удалить {{ $product->title }}" title="Удалить">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <footer class="admin-cart__footer">
                <div class="admin-cart__selection-total">
                    Выбрано: <strong data-cart-selected-count>0</strong> шт. ·
                    <strong data-cart-selected-total>0,00 ₽</strong>
                </div>
                <button type="submit" class="orders-page__btn orders-page__btn--success">
                    Оформить выбранное
                </button>
            </footer>
        </form>
    @else
        <div class="orders-page__empty admin-cart__empty">
            <span class="orders-page__empty-icon"><i class="fas fa-shopping-cart" aria-hidden="true"></i></span>
            Корзина пуста. Добавьте товары из раздела «Поиск деталей».
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('adminCartOrderForm');
    if (!form) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const formatCurrency = value => new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value) + ' ₽';

    const updateSelectedTotal = () => {
        let count = 0;
        let total = 0;
        form.querySelectorAll('[data-cart-select]:checked').forEach(checkbox => {
            const row = checkbox.closest('[data-cart-row]');
            const quantity = parseInt(row.querySelector('[data-cart-quantity]').value || '1', 10) || 1;
            count += quantity;
            total += (parseFloat(row.dataset.price) || 0) * quantity;
        });
        form.querySelector('[data-cart-selected-count]').textContent = String(count);
        form.querySelector('[data-cart-selected-total]').textContent = formatCurrency(total);
    };

    form.querySelector('[data-cart-select-all]').addEventListener('change', event => {
        form.querySelectorAll('[data-cart-select]').forEach(checkbox => { checkbox.checked = event.target.checked; });
        updateSelectedTotal();
    });

    form.addEventListener('change', event => {
        if (event.target.matches('[data-cart-select]')) updateSelectedTotal();
        if (event.target.matches('[data-cart-quantity]')) {
            const input = event.target;
            const quantity = Math.max(1, Math.min(999, parseInt(input.value || '1', 10) || 1));
            input.value = String(quantity);
            fetch(input.dataset.updateUrl, {
                method: 'PUT',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ quantity }),
            }).then(async response => {
                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw new Error(data.message || 'Не удалось изменить количество.');
                }
                window.location.reload();
            }).catch(error => window.alert(error.message));
        }
    });

    form.addEventListener('click', event => {
        const remove = event.target.closest('[data-cart-remove]');
        if (!remove) return;
        fetch(remove.dataset.removeUrl, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then(async response => {
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || 'Не удалось удалить товар.');
            }
            window.location.reload();
        }).catch(error => window.alert(error.message));
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const items = Array.from(form.querySelectorAll('[data-cart-select]:checked')).map(checkbox => {
            const row = checkbox.closest('[data-cart-row]');
            return {
                product_id: parseInt(checkbox.value, 10),
                quantity: parseInt(row.querySelector('[data-cart-quantity]').value || '1', 10) || 1,
            };
        });
        if (!items.length) {
            window.alert('Выберите товары для оформления.');
            return;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ items }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Не удалось оформить заказ.');
            if (data.redirect) window.location.assign(data.redirect);
            else window.alert(data.message || 'Заказ оформлен.');
        } catch (error) {
            window.alert(error.message);
        }
    });

    updateSelectedTotal();
})();
</script>
@endpush
