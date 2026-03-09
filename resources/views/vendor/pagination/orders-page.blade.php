@if ($paginator->hasPages())
<nav class="orders-page__pagination" aria-label="Навигация по страницам">
    <div class="orders-page__pagination-mobile">
        <ul class="orders-page__pagination-list">
            @if ($paginator->onFirstPage())
                <li class="orders-page__pagination-item orders-page__pagination-item--disabled" aria-disabled="true">
                    <span class="orders-page__pagination-link">@lang('pagination.previous')</span>
                </li>
            @else
                <li class="orders-page__pagination-item">
                    <a class="orders-page__pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
                </li>
            @endif
            @if ($paginator->hasMorePages())
                <li class="orders-page__pagination-item">
                    <a class="orders-page__pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                </li>
            @else
                <li class="orders-page__pagination-item orders-page__pagination-item--disabled" aria-disabled="true">
                    <span class="orders-page__pagination-link">@lang('pagination.next')</span>
                </li>
            @endif
        </ul>
    </div>
    <div class="orders-page__pagination-desktop">
        <p class="orders-page__pagination-info">
            {!! __('Showing') !!}
            <span class="orders-page__pagination-info-num">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span class="orders-page__pagination-info-num">{{ $paginator->lastItem() }}</span>
            {!! __('of') !!}
            <span class="orders-page__pagination-info-num">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>
        <ul class="orders-page__pagination-list">
            @if ($paginator->onFirstPage())
                <li class="orders-page__pagination-item orders-page__pagination-item--disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="orders-page__pagination-link" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li class="orders-page__pagination-item">
                    <a class="orders-page__pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="orders-page__pagination-item orders-page__pagination-item--disabled" aria-disabled="true"><span class="orders-page__pagination-link">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="orders-page__pagination-item orders-page__pagination-item--active" aria-current="page"><span class="orders-page__pagination-link">{{ $page }}</span></li>
                        @else
                            <li class="orders-page__pagination-item"><a class="orders-page__pagination-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <li class="orders-page__pagination-item">
                    <a class="orders-page__pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class="orders-page__pagination-item orders-page__pagination-item--disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="orders-page__pagination-link" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </div>
</nav>
@endif
