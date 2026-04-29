<template id="admin-search-results-table-shell">
    <div class="table-responsive admin-search__table-wrapper"
         data-search-context="results"
         data-current-query="">
        <table class="admin-search__table">
            <thead>
            <tr>
                <th class="admin-search__cell--checkbox">
                    <label class="custom-checkbox">
                        <input type="checkbox" class="custom-checkbox__input" onclick="window.adminSearchToggleAll(this)">
                        <span class="custom-checkbox__box" aria-hidden="true"></span>
                    </label>
                </th>
                <th class="admin-search__cell--image"></th>
                <th class="admin-search__cell--sku">Артикул / Бренд</th>
                <th class="admin-search__cell--title">Наименование</th>
                <th class="admin-search__cell--price">Цена, руб.</th>
                <th class="admin-search__cell--qty">Кол-во</th>
                <th class="admin-search__cell--total">Сумма, руб.</th>
                <th class="admin-search__cell--link"></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</template>
