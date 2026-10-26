@php
    $postForProduct = $post ?? null;
    $selectedProductType = old('related_product_type', $relatedProduct ? $postForProduct?->related_product_type : null);
    $selectedProductId = old('related_product_id', $relatedProduct ? $postForProduct?->related_product_id : null);
    $selectedProductTitle = old('related_product_title', $relatedProduct?->title ?? '');
@endphp

<div class="admin-card mb-4">
    <div class="admin-card-header">
        <div class="d-flex align-items-center">
            <i class="fas fa-link text-warning mr-2"></i>
            <span>Связанная запчасть</span>
        </div>
    </div>
    <div class="card-body p-4">
        <label for="related_product_search" class="font-weight-bold text-dark">Найдите товар в каталоге</label>
        <div class="input-group">
            <input
                type="search"
                id="related_product_search"
                class="form-control"
                value="{{ $selectedProductTitle }}"
                placeholder="Название, SKU/OEM, модель или производитель"
                autocomplete="off"
                data-endpoint="{{ route('admin.posts.related-products') }}"
            >
            <div class="input-group-append">
                <button type="button" id="related_product_clear" class="btn btn-outline-secondary">Убрать связь</button>
            </div>
        </div>
        <input type="hidden" name="related_product_type" id="related_product_type" value="{{ $selectedProductType }}">
        <input type="hidden" name="related_product_id" id="related_product_id" value="{{ $selectedProductId }}">
        <input type="hidden" name="related_product_title" id="related_product_title" value="{{ $selectedProductTitle }}">
        @error('related_product_type') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
        @error('related_product_id') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
        <div id="related_product_results" class="list-group mt-2" role="listbox"></div>
        <small id="related_product_status" class="form-text text-muted" aria-live="polite">
            {{ $selectedProductTitle ? 'Выбрано: '.$selectedProductTitle : 'Можно оставить поле пустым.' }}
        </small>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const search = document.getElementById('related_product_search');
                if (!search) return;

                const typeField = document.getElementById('related_product_type');
                const idField = document.getElementById('related_product_id');
                const titleField = document.getElementById('related_product_title');
                const results = document.getElementById('related_product_results');
                const status = document.getElementById('related_product_status');
                const clear = document.getElementById('related_product_clear');
                let timer;
                let requestId = 0;

                const clearSelection = () => {
                    typeField.value = '';
                    idField.value = '';
                    titleField.value = '';
                };

                clear.addEventListener('click', () => {
                    requestId += 1;
                    window.clearTimeout(timer);
                    clearSelection();
                    search.value = '';
                    results.replaceChildren();
                    status.textContent = 'Связанная запчасть удалена.';
                });

                search.addEventListener('input', () => {
                    const currentRequest = ++requestId;
                    clearSelection();
                    results.replaceChildren();
                    status.textContent = 'Ищем в каталоге…';
                    window.clearTimeout(timer);

                    const query = search.value.trim();
                    if (query.length < 2) {
                        status.textContent = 'Введите не менее двух символов или оставьте поле пустым.';
                        return;
                    }

                    timer = window.setTimeout(async () => {
                        try {
                            const url = new URL(search.dataset.endpoint, window.location.origin);
                            url.searchParams.set('q', query);
                            const response = await fetch(url, { headers: { Accept: 'application/json' } });
                            if (!response.ok) throw new Error('Search failed');
                            const payload = await response.json();
                            if (currentRequest !== requestId) return;
                            const products = Array.isArray(payload.data) ? payload.data : [];
                            results.replaceChildren();

                            products.forEach((product) => {
                                const option = document.createElement('button');
                                option.type = 'button';
                                option.className = 'list-group-item list-group-item-action';
                                const title = document.createElement('strong');
                                title.textContent = product.title || 'Без названия';
                                const details = document.createElement('small');
                                details.className = 'd-block text-muted';
                                details.textContent = [product.product_type, product.manufacturer, product.model, product.sku].filter(Boolean).join(' · ');
                                option.append(title, details);
                                option.addEventListener('click', () => {
                                    requestId += 1;
                                    typeField.value = product.type;
                                    idField.value = product.id;
                                    titleField.value = product.title || '';
                                    search.value = product.title || '';
                                    results.replaceChildren();
                                    status.textContent = `Выбрано: ${product.title}`;
                                });
                                results.append(option);
                            });

                            status.textContent = products.length ? 'Выберите один товар из списка.' : 'Ничего не найдено.';
                        } catch (error) {
                            if (currentRequest === requestId) {
                                status.textContent = 'Не удалось выполнить поиск. Попробуйте ещё раз.';
                            }
                        }
                    }, 250);
                });
            });
        </script>
    @endpush
@endonce
