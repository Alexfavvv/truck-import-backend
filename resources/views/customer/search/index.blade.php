@extends('customer.layouts.app')

@section('content')
    @php
        $currentResultIds = $products->isNotEmpty() ? $products->getCollection()->pluck('id')->implode(',') : '';
    @endphp
    <section class="admin-search" data-current-result-ids="{{ $currentResultIds }}" data-current-query="{{ $query }}">
        <div class="admin-search__container">
            <header class="admin-search__header">
                <div class="admin-search__header-left">
                    <h1 class="admin-search__title">Поиск деталей</h1>
                </div>
            </header>

            {{-- 1. Поиск --}}
            <form action="{{ route('customer.search.index') }}" method="GET" class="admin-search__form">
                <div class="admin-search__form-group">
                    <input type="text"
                           name="q"
                           class="admin-search__input"
                           placeholder="Артикул или название детали..."
                           value="{{ old('q', $query) }}"
                           autofocus>
                    <button type="submit" class="admin-search__submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            {{-- 2. Результаты поиска --}}
            <div class="admin-search__results-card">
                <div class="admin-search__results-header">
                    <h3 class="admin-search__results-title">
                        <span>Результаты поиска</span>
                    </h3>
                    <!-- @if($query !== '')
                        <span class="admin-search__badge">запрос: «{{ $query }}»</span>
                    @endif -->
                </div>
                <div class="admin-search__results-body">
                    @if($query === '')
                        <p class="admin-search__empty-text">Введите запрос в поле выше и нажмите «Искать».</p>
                    @elseif($products->isEmpty())
                        <p class="admin-search__empty-text">По запросу «{{ $query }}» ничего не найдено.</p>
                    @else
                        <div class="table-responsive admin-search__table-wrapper"
                             data-search-context="results"
                             data-current-query="{{ $query }}">
                            <table class="admin-search__table">
                                <thead>
                                <tr>
                                    <th class="admin-search__cell--checkbox">
                                        <label class="custom-checkbox">
                                            <input type="checkbox" class="custom-checkbox__input" onclick="window.adminSearchToggleAll(this)">
                                            <span class="custom-checkbox__box" aria-hidden="true"></span>
                                        </label>
                                    </th>
                                    <th class="admin-search__cell--image"> </th>
                                    <th class="admin-search__cell--sku">Артикул / Бренд</th>
                                    <th class="admin-search__cell--title">Наименование</th>
                                    <th class="admin-search__cell--price">Цена, руб.</th>
                                    <th class="admin-search__cell--qty">Кол-во</th>
                                    <th class="admin-search__cell--total">Сумма, руб.</th>
                                    <th class="admin-search__cell--link"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($products as $product)
                                    @php
                                        $rawPrice = $product->price ?? 0;
                                        $formattedPrice = number_format($rawPrice, 2, ',', ' ') . ' ₽';
                                    @endphp
                                    <tr class="admin-search__row"
                                        data-price="{{ $rawPrice }}"
                                        data-product-id="{{ $product->id }}">
                                        <td class="admin-search__cell admin-search__cell--checkbox">
                                            <label class="custom-checkbox">
                                                <input type="checkbox" class="custom-checkbox__input" name="items[]" value="{{ $product->id ?? $product->sku }}">
                                                <span class="custom-checkbox__box" aria-hidden="true"></span>
                                            </label>
                                        </td>
                                        <td class="admin-search__cell admin-search__cell--image">
                                            @if($product->image_path)
                                                <img src="{{ $product->image_url }}" alt=""
                                                     class="admin-search__product-image">
                                            @else
                                                <i class="far fa-image text-muted" style="font-size: 3em;"></i>
                                            @endif
                                        </td>
                                        <td class="admin-search__cell admin-search__cell--sku">
                                            <div class="admin-search__sku">{{ $product->sku }}</div>
                                            <div class="admin-search__brand">{{ $product->brand->title ?? '—' }}</div>
                                        </td>
                                        <td class="admin-search__cell admin-search__cell--title">
                                            <span class="admin-search__product-title">{{ $product->title }}</span>
                                        </td>
                                        <td class="admin-search__cell admin-search__cell--price">
                                            <span class="admin-search__price js-item-price" data-raw="{{ $rawPrice }}">{{ $formattedPrice }}</span>
                                        </td>
                                        <td class="admin-search__cell admin-search__cell--qty">
                                            <div class="admin-search__qty-control">
                                                <button type="button"
                                                        class="admin-search__qty-btn admin-search__qty-btn--minus"
                                                        data-qty-action="minus">
                                                    −
                                                </button>
                                                <input type="number"
                                                       class="admin-search__qty-input form-control form-control-sm js-item-qty"
                                                       min="1"
                                                       value="1">
                                                <button type="button"
                                                        class="admin-search__qty-btn admin-search__qty-btn--plus"
                                                        data-qty-action="plus">
                                                    +
                                                </button>
                                            </div>
                                        </td>
                                        <td class="admin-search__cell admin-search__cell--total">
                                            <span class="admin-search__total js-item-total">{{ $formattedPrice }}</span>
                                        </td>
                                        <td class="admin-search__cell admin-search__cell--link">
                                            <a href="{{ rtrim(config('app.frontend_url'), '/') . '/catalog/' . $product->sku }}"
                                               target="_blank"
                                               class="admin-search__link">
                                                К товару
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($products, 'links'))
                            <div class="admin-search__results-footer">
                                {{ $products->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- 3. Предыдущие поиски --}}
            <div class="admin-search__history-card">
                <div class="admin-search__history-card-header">
                    <h3 class="admin-search__history-card-title">
                        <span>Предыдущие поиски</span>
                    </h3>
                </div>
                <div class="admin-search__history-card-body">
                    @php
                        $hasPreviousResults = isset($previousProducts) && $previousProducts->isNotEmpty();
                    @endphp

                    @if(!$hasPreviousResults)
                        <p class="admin-search__empty-text">Пока не было поисков в этой сессии.</p>
                    @else
                        <div class="admin-search__history-block">
                            <div class="table-responsive admin-search__table-wrapper"
                                 data-search-context="history">
                                <table class="admin-search__table">
                                    <thead>
                                    <tr>
                                        <th class="admin-search__cell--checkbox">
                                            <label class="custom-checkbox">
                                                <input type="checkbox" class="custom-checkbox__input" onclick="window.adminSearchToggleAll(this)">
                                                <span class="custom-checkbox__box" aria-hidden="true"></span>
                                            </label>
                                        </th>
                                        <th class="admin-search__cell--image"> </th>
                                        <th class="admin-search__cell--sku">Артикул / Бренд</th>
                                        <th class="admin-search__cell--title">Наименование</th>
                                        <th class="admin-search__cell--price">Цена, руб.</th>
                                        <th class="admin-search__cell--qty">Кол-во</th>
                                        <th class="admin-search__cell--total">Сумма, руб.</th>
                                        <th class="admin-search__cell--link"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($previousProducts as $item)
                                        @php
                                            $rawPrice = $item->price ?? 0;
                                            $formattedPrice = number_format($rawPrice, 2, ',', ' ') . ' ₽';
                                        @endphp
                                        <tr class="admin-search__row"
                                            data-price="{{ $rawPrice }}"
                                            data-product-id="{{ $item->id }}">
                                            <td class="admin-search__cell admin-search__cell--checkbox">
                                                <label class="custom-checkbox">
                                                    <input type="checkbox" class="custom-checkbox__input" name="items[]" value="{{ $item->id ?? $item->sku }}">
                                                    <span class="custom-checkbox__box" aria-hidden="true"></span>
                                                </label>
                                            </td>
                                            <td class="admin-search__cell admin-search__cell--image">
                                                @if($item->image_path)
                                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}"
                                                         class="admin-search__product-image">
                                                @else
                                                    <i class="far fa-image text-muted" style="font-size: 3em;"></i>
                                                @endif
                                            </td>
                                            <td class="admin-search__cell admin-search__cell--sku">
                                                <div class="admin-search__sku">{{ $item->sku }}</div>
                                                <div class="admin-search__brand">{{ $item->brand->title ?? '—' }}</div>
                                            </td>
                                            <td class="admin-search__cell admin-search__cell--title">
                                                <span class="admin-search__product-title">{{ $item->title }}</span>
                                            </td>
                                            <td class="admin-search__cell admin-search__cell--price">
                                                <span class="admin-search__price js-item-price" data-raw="{{ $rawPrice }}">{{ $formattedPrice }}</span>
                                            </td>
                                            <td class="admin-search__cell admin-search__cell--qty">
                                                <div class="admin-search__qty-control">
                                                    <button type="button"
                                                            class="admin-search__qty-btn admin-search__qty-btn--minus"
                                                            data-qty-action="minus">
                                                        −
                                                    </button>
                                                    <input type="number"
                                                           class="admin-search__qty-input form-control form-control-sm js-item-qty"
                                                           min="1"
                                                           value="1">
                                                    <button type="button"
                                                            class="admin-search__qty-btn admin-search__qty-btn--plus"
                                                            data-qty-action="plus">
                                                        +
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="admin-search__cell admin-search__cell--total">
                                                <span class="admin-search__total js-item-total">{{ $formattedPrice }}</span>
                                            </td>
                                            <td class="admin-search__cell admin-search__cell--link">
                                                <a href="{{ rtrim(config('app.frontend_url'), '/') . '/catalog/' . $item->sku }}"
                                                   target="_blank"
                                                   class="admin-search__link">
                                                    К товару
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4. Панель действий с выбранными товарами --}}
        <div class="admin-search__actions-bar"
             data-cart-add-url="{{ route('admin.cart.add') }}">
             <div class="admin-search__actions-bar-container">
            <div class="admin-search__actions-left">
                <button type="button"
                        class="admin-search__btn admin-search__btn--danger"
                        data-admin-search-action="delete-selected">
                    Удалить выбранное
                </button>
            </div>
            <div class="admin-search__actions-right">
                <button type="button"
                        class="admin-search__btn admin-search__btn--secondary"
                        data-admin-search-action="send-request">
                    Отправить запрос
                </button>
                <button type="button"
                        class="admin-search__btn admin-search__btn--primary"
                        data-admin-search-action="add-to-cart">
                    В корзину
                </button>
                <div class="admin-search__summary" data-admin-search-summary>
                    Итого: <span data-admin-search-summary-qty>0</span> шт.,
                    <span data-admin-search-summary-total>0,00 ₽</span>
                </div>
            </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const STORAGE_KEY_DELETED = 'adminSearchDeletedByContext';
        const STORAGE_KEY_LAST_QUERY = 'adminSearchLastQuery';

        window.adminSearchToggleAll = function (masterCheckbox) {
            const table = masterCheckbox.closest('table');
            if (!table) return;
            const checkboxes = table.querySelectorAll('tbody input[type="checkbox"][name="items[]"]');
            checkboxes.forEach(cb => {
                cb.checked = masterCheckbox.checked;
            });
            if (typeof window.adminSearchUpdateSummary === 'function') {
                window.adminSearchUpdateSummary();
            }
        };

        function getStorageKey(wrapper) {
            if (!wrapper || !wrapper.dataset.searchContext) return null;
            const ctx = wrapper.dataset.searchContext;
            if (ctx === 'results') {
                const q = (wrapper.dataset.currentQuery || '').trim();
                return 'results::' + (q || '__empty__');
            }
            if (ctx === 'history') return 'history';
            return null;
        }

        (function () {
            const tables = document.querySelectorAll('.admin-search__table');
            if (!tables.length) return;

            // Восстанавливаем скрытие удалённых товаров по контексту (результаты или история)
            const section = document.querySelector('.admin-search');
            const currentResultIdsRaw = section && section.dataset.currentResultIds ? section.dataset.currentResultIds : '';
            const currentResultIds = new Set(currentResultIdsRaw.split(',').map(Number).filter(Number.isInteger));
            const currentQuery = (section && section.dataset.currentQuery) ? String(section.dataset.currentQuery).trim() : '';

            try {
                let raw = sessionStorage.getItem(STORAGE_KEY_DELETED);
                let byKey = raw ? JSON.parse(raw) : {};
                if (typeof byKey !== 'object' || byKey === null) byKey = {};

                const lastQuery = sessionStorage.getItem(STORAGE_KEY_LAST_QUERY);
                const isNewSearch = currentQuery !== lastQuery;

                if (isNewSearch) {
                    sessionStorage.setItem(STORAGE_KEY_LAST_QUERY, currentQuery);
                    // Только при новом поиске (не при перезагрузке) убираем из «удалённых» ID текущих результатов
                    if (currentResultIds.size > 0 && byKey.history && Array.isArray(byKey.history)) {
                        byKey.history = byKey.history.filter(id => !currentResultIds.has(Number(id)));
                        sessionStorage.setItem(STORAGE_KEY_DELETED, JSON.stringify(byKey));
                    }
                }

                document.querySelectorAll('.admin-search__row[data-product-id]').forEach(row => {
                    const wrapper = row.closest('[data-search-context]');
                    const key = getStorageKey(wrapper);
                    if (!key || !byKey[key] || !Array.isArray(byKey[key])) return;
                    const set = new Set(byKey[key].map(Number));
                    const id = parseInt(row.dataset.productId, 10);
                    if (!Number.isInteger(id) || !set.has(id)) return;
                    if (key === 'history' && currentResultIds.has(id)) return;
                    row.remove();
                });
            } catch (e) {
                sessionStorage.removeItem(STORAGE_KEY_DELETED);
                sessionStorage.removeItem(STORAGE_KEY_LAST_QUERY);
            }

            const formatCurrency = value => {
                try {
                    return new Intl.NumberFormat('ru-RU', {
                        style: 'currency',
                        currency: 'RUB',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(value);
                } catch (e) {
                    return value.toFixed(2) + ' ₽';
                }
            };

            const updateRowTotal = row => {
                const qtyInput = row.querySelector('.js-item-qty');
                const totalEl = row.querySelector('.js-item-total');
                const priceEl = row.querySelector('.js-item-price');
                if (!qtyInput || !totalEl || !priceEl) return;

                const rawPrice = parseFloat(priceEl.dataset.raw || '0') || 0;
                let qty = parseInt(qtyInput.value || '1', 10);
                if (qty < 1 || Number.isNaN(qty)) {
                    qty = 1;
                    qtyInput.value = '1';
                }

                const sum = rawPrice * qty;
                totalEl.textContent = formatCurrency(sum);
            };

            tables.forEach(table => {
                table.querySelectorAll('tbody tr').forEach(row => {
                    const qtyInput = row.querySelector('.js-item-qty');
                    if (qtyInput) {
                        qtyInput.addEventListener('input', () => {
                            updateRowTotal(row);
                            updateSummary();
                        });
                        qtyInput.addEventListener('change', () => {
                            updateRowTotal(row);
                            updateSummary();
                        });
                    }

                    const qtyButtons = row.querySelectorAll('[data-qty-action]');
                    qtyButtons.forEach(button => {
                        button.addEventListener('click', () => {
                            if (!qtyInput) return;
                            let qty = parseInt(qtyInput.value || '1', 10);
                            if (Number.isNaN(qty) || qty < 1) {
                                qty = 1;
                            }

                            if (button.dataset.qtyAction === 'plus') {
                                qty += 1;
                            } else if (button.dataset.qtyAction === 'minus' && qty > 1) {
                                qty -= 1;
                            }

                            qtyInput.value = String(qty);
                            updateRowTotal(row);
                            updateSummary();
                        });
                    });

                    if (qtyInput) {
                        updateRowTotal(row);
                    }
                });
            });

            const allItemCheckboxes = document.querySelectorAll('.admin-search__row input[type="checkbox"][name="items[]"]');
            allItemCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    updateSummary();
                });
            });

            const getSelectedRows = () => {
                const checkboxes = document.querySelectorAll('.admin-search__row input[type="checkbox"][name="items[]"]:checked');
                return Array.from(checkboxes).map(cb => cb.closest('.admin-search__row')).filter(Boolean);
            };

            const deleteSelected = () => {
                const rows = getSelectedRows();
                if (rows.length === 0) return;
                const wrapper = rows[0].closest('[data-search-context]');
                const key = getStorageKey(wrapper);
                const toDelete = [];
                rows.forEach(row => {
                    const id = row.dataset.productId ? parseInt(row.dataset.productId, 10) : null;
                    if (Number.isInteger(id)) {
                        toDelete.push(id);
                    }
                    row.remove();
                });
                if (toDelete.length > 0 && key) {
                    try {
                        const raw = sessionStorage.getItem(STORAGE_KEY_DELETED);
                        const byKey = raw ? JSON.parse(raw) : {};
                        if (typeof byKey !== 'object' || byKey === null) byKey = {};
                        const current = Array.isArray(byKey[key]) ? byKey[key] : [];
                        byKey[key] = [...new Set([...current, ...toDelete])];
                        sessionStorage.setItem(STORAGE_KEY_DELETED, JSON.stringify(byKey));
                    } catch (e) {
                        const byKey = {};
                        byKey[key] = toDelete;
                        sessionStorage.setItem(STORAGE_KEY_DELETED, JSON.stringify(byKey));
                    }
                }
                updateSummary();
            };

            const getSelectedItems = () => {
                const rows = getSelectedRows();
                return rows.map(row => {
                    const productId = row.dataset.productId ? parseInt(row.dataset.productId, 10) : null;
                    const qtyInput = row.querySelector('.js-item-qty');
                    let quantity = qtyInput ? parseInt(qtyInput.value || '1', 10) : 1;
                    if (Number.isNaN(quantity) || quantity < 1) {
                        quantity = 1;
                    }
                    return { row, productId, quantity };
                }).filter(item => Number.isInteger(item.productId) && item.productId > 0);
            };

            const updateSummary = () => {
                const qtyEl = document.querySelector('[data-admin-search-summary-qty]');
                const totalEl = document.querySelector('[data-admin-search-summary-total]');
                if (!qtyEl || !totalEl) return;

                const rows = getSelectedRows();
                let totalQty = 0;
                let totalSum = 0;

                rows.forEach(row => {
                    const qtyInput = row.querySelector('.js-item-qty');
                    const priceEl = row.querySelector('.js-item-price');
                    if (!priceEl) return;

                    const rawPrice = parseFloat(priceEl.dataset.raw || '0') || 0;
                    let qty = qtyInput ? parseInt(qtyInput.value || '1', 10) : 1;
                    if (Number.isNaN(qty) || qty < 1) {
                        qty = 1;
                    }
                    totalQty += qty;
                    totalSum += rawPrice * qty;
                });

                qtyEl.textContent = String(totalQty);
                totalEl.textContent = formatCurrency(totalSum);
            };

            window.adminSearchUpdateSummary = updateSummary;

            const addToCartViaAdmin = async () => {
                const selected = getSelectedItems();
                if (!selected.length) {
                    alert('Не выбраны товары.');
                    return;
                }

                const bar = document.querySelector('.admin-search__actions-bar');
                const url = bar?.getAttribute('data-cart-add-url') || '{{ route("admin.cart.add") }}';
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.content : null;

                const payload = {
                    items: selected.map(item => ({
                        product_id: item.productId,
                        quantity: item.quantity,
                    })),
                };

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        let message = data.message || 'Ошибка добавления в корзину (код ' + response.status + ')';
                        if (Array.isArray(data.errors) && data.errors.length) {
                            message = data.errors.join('\n');
                        }
                        alert(message);
                        return;
                    }

                    alert(data.message || 'Товары добавлены в корзину.');
                } catch (e) {
                    alert('Сетевая ошибка при добавлении в корзину.');
                }
            };

            const sendRequestStub = () => {
                alert('Отправка запроса пока не реализована.');
            };

            const actions = document.querySelectorAll('[data-admin-search-action]');
            actions.forEach(button => {
                button.addEventListener('click', () => {
                    const action = button.dataset.adminSearchAction;
                    (async () => {
                        if (action === 'delete-selected') {
                            deleteSelected();
                        } else if (action === 'add-to-cart') {
                            await addToCartViaAdmin();
                        } else if (action === 'send-request') {
                            sendRequestStub();
                        }
                    })();
                });
            });
        })();
    </script>
@endpush
