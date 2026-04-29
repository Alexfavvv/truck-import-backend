@php
    $rawPrice = $product->price ?? 0;
    $formattedPrice = number_format($rawPrice, 2, ',', ' ') . ' ₽';
    $qty = (int) ($quantity ?? 1);
    if ($qty < 1) {
        $qty = 1;
    }
    $rowChecked = $checked ?? false;
@endphp
<tr class="admin-search__row"
    data-price="{{ $rawPrice }}"
    data-product-id="{{ $product->id }}">
    <td class="admin-search__cell admin-search__cell--checkbox">
        <label class="custom-checkbox">
            <input type="checkbox" class="custom-checkbox__input" name="items[]" value="{{ $product->id ?? $product->sku }}" @if($rowChecked) checked @endif>
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
                   value="{{ $qty }}">
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
