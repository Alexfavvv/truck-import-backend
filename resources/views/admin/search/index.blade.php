@extends('admin.layouts.main')

@section('content')
    @php
        $currentResultIds = $products->isNotEmpty() ? $products->getCollection()->pluck('id')->implode(',') : '';
        $storefrontCartUrl = config('app.frontend_cart_url');
    @endphp
    <section class="admin-search"
             data-current-result-ids="{{ $currentResultIds }}"
             data-current-query="{{ $query }}"
             data-upload-list-url="{{ route('admin.search.upload-list') }}"
             data-create-order-url="{{ route('admin.search.create-order') }}"
             data-storefront-cart="{{ $storefrontCartUrl }}">
        <div class="admin-search__container">
            <header class="admin-search__header">
                <div class="admin-search__header-left">
                    <h1 class="admin-search__title">Поиск деталей</h1>
                </div>
            </header>

            {{-- 1. Поиск --}}
            <form action="{{ route('admin.search.index') }}" method="GET" class="admin-search__form">
                <div class="admin-search__form-row">
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
                    <div class="admin-search__form-tools">
                        <button type="button"
                                class="admin-search__tool-btn"
                                id="adminSearchUploadListBtn"
                                aria-label="Загрузить список из файла">
                            <i class="fas fa-upload"></i>
                            <span>Загрузить список</span>
                        </button>
                        <button type="button"
                                class="admin-search__tool-btn admin-search__tool-btn--icon"
                                id="adminSearchUploadHelpBtn"
                                aria-label="Справка по загрузке списка"
                                title="Справка">
                            ?
                        </button>
                    </div>
                </div>
                <input type="file"
                       id="adminSearchUploadListInput"
                       accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv"
                       hidden>
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
                                    @include('partials.search-product-table-row', ['product' => $product, 'quantity' => 1, 'checked' => false])
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
                                        @include('partials.search-product-table-row', ['product' => $item, 'quantity' => 1, 'checked' => false])
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
             data-cart-add-url="{{ route('admin.cart.add') }}"
             data-storefront-cart="{{ $storefrontCartUrl }}">
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

        @include('partials.admin-search-results-table-shell')

        <dialog class="admin-search__help-dialog" id="adminSearchUploadHelpDialog">
            <div class="admin-search__help-dialog-inner">
                <h2>Загрузка списка деталей</h2>
                <p>Подготовьте файл Excel (.xlsx, .xls) или CSV.</p>
                <ul>
                    <li>В <strong>первом столбце</strong> укажите артикул детали так же, как в каталоге.</li>
                    <li>Во <strong>втором столбце</strong> — необходимое количество (целое число, не меньше 1).</li>
                    <li>Первая строка может быть заголовком («Артикул», «Количество» и т.п.) — она будет пропущена.</li>
                </ul>
                <p>Совпавшие позиции появятся в блоке «Результаты поиска», будут отмечены галочкой, количество подставится из файла. Если строка с этой деталью уже есть в таблице, количество суммируется.</p>
                <button type="button" class="admin-search__help-dialog-close" onclick="this.closest('dialog').close()">
                    Понятно
                </button>
            </div>
        </dialog>
    </section>
@endsection

@push('scripts')
    @include('partials.admin-search-scripts')
@endpush
