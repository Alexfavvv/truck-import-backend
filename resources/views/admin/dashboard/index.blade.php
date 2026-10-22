@extends('admin.layouts.main')

@section('title', 'Дашборд — Truck Import')

@section('content')
@php
    $metrics = $dashboard['metrics'];
    $cards = [
        [
            'title' => 'Заказы',
            'value' => number_format($metrics['orders']['value'], 0, ',', ' '),
            'metric' => $metrics['orders'],
            'difference' => number_format(abs($metrics['orders']['difference']), 0, ',', ' ').' заказов',
            'href' => route('admin.orders.index', ['tab' => 'company']),
            'primary' => true,
        ],
        [
            'title' => 'Выручка',
            'value' => number_format($metrics['revenue']['value'], 2, ',', ' ').' ₽',
            'metric' => $metrics['revenue'],
            'difference' => number_format(abs($metrics['revenue']['difference']), 2, ',', ' ').' ₽',
            'href' => route('admin.invoices.index', ['tab' => 'company', 'status' => 'paid']),
            'primary' => false,
        ],
        [
            'title' => 'Новые клиенты',
            'value' => number_format($metrics['clients']['value'], 0, ',', ' '),
            'metric' => $metrics['clients'],
            'difference' => number_format(abs($metrics['clients']['difference']), 0, ',', ' ').' компаний',
            'href' => route('admin.users.index'),
            'primary' => false,
        ],
        [
            'title' => 'Оплаченные счета',
            'value' => number_format($metrics['paid_invoices']['value'], 0, ',', ' '),
            'metric' => $metrics['paid_invoices'],
            'difference' => number_format(abs($metrics['paid_invoices']['difference']), 0, ',', ' ').' счетов',
            'href' => route('admin.invoices.index', ['tab' => 'company', 'status' => 'paid']),
            'primary' => false,
        ],
    ];
@endphp

<main class="admin-dashboard">
    <div class="admin-dashboard__top">
        <div>
            <p class="admin-dashboard__eyebrow">Truck Import · аналитика</p>
            <h1 class="admin-dashboard__title">Дашборд @if($demoMode)<span class="admin-dashboard__demo-badge">Демо-данные</span>@endif</h1>
            <p class="admin-dashboard__period-caption">{{ $dashboard['period']['range_label'] }} · время приложения: {{ config('app.timezone') }}</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-dashboard__period-form">
            @if($demoMode)<input type="hidden" name="demo" value="1">@endif
            <label for="dashboard-period">Период</label>
            <select id="dashboard-period" name="period" onchange="this.form.submit()">
                @foreach($dashboard['periods'] as $key => $label)
                    <option value="{{ $key }}" @selected($dashboard['period']['key'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <section class="admin-dashboard__kpis" aria-label="Ключевые показатели">
        @foreach($cards as $card)
            @php
                $change = $card['metric']['change'];
                $difference = $card['metric']['difference'];
                $direction = $change === null ? 'neutral' : ($change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'));
            @endphp
            <a href="{{ $card['href'] }}" class="admin-dashboard__kpi {{ $card['primary'] ? 'admin-dashboard__kpi--primary' : '' }}">
                <span class="admin-dashboard__kpi-title">{{ $card['title'] }}</span>
                <strong class="admin-dashboard__kpi-value">{{ $card['value'] }}</strong>
                <span class="admin-dashboard__change admin-dashboard__change--{{ $direction }}">
                    @if($change === null)
                        Нет данных для сравнения
                    @else
                        {{ $change > 0 ? '↑' : ($change < 0 ? '↓' : '→') }} {{ number_format(abs($change), 1, ',', ' ') }}% {{ $dashboard['period']['comparison_label'] }}
                    @endif
                </span>
                <span class="admin-dashboard__kpi-difference">
                    {{ $difference > 0 ? '+' : ($difference < 0 ? '−' : '') }}{{ $card['difference'] }} {{ $dashboard['period']['comparison_label'] }}
                </span>
            </a>
        @endforeach
    </section>

    <section class="admin-dashboard__panel admin-dashboard__panel--chart" aria-labelledby="dashboard-chart-title">
        <div class="admin-dashboard__panel-heading">
            <div>
                <p class="admin-dashboard__eyebrow">Динамика</p>
                <h2 id="dashboard-chart-title">Заказы и выручка</h2>
                <p class="admin-dashboard__muted">Заказы — по дате создания, выручка — по дате оплаты счёта</p>
            </div>
            <div class="admin-dashboard__chart-switch" role="group" aria-label="Показатель графика">
                <button type="button" class="is-active" data-dashboard-series="orders" aria-pressed="true">Заказы</button>
                <button type="button" data-dashboard-series="revenue" aria-pressed="false">Выручка</button>
            </div>
        </div>
        <div class="admin-dashboard__chart-wrap" data-dashboard-chart>
            <canvas class="admin-dashboard__canvas" role="img" aria-label="Динамика заказов и выручки за выбранный период"></canvas>
            <div class="admin-dashboard__chart-tooltip" role="status" hidden></div>
        </div>
        <p class="admin-dashboard__chart-hint">Наведите на точку графика, чтобы увидеть значение.</p>
        <script type="application/json" id="admin-dashboard-chart-data">@json($dashboard['chart'])</script>
    </section>

    <div class="admin-dashboard__insights">
        <section class="admin-dashboard__panel" aria-labelledby="dashboard-managers-title">
            <div class="admin-dashboard__panel-heading">
                <div>
                    <p class="admin-dashboard__eyebrow">Команда</p>
                    <h2 id="dashboard-managers-title">Рейтинг менеджеров</h2>
                </div>
                <span class="admin-dashboard__tag">Топ 5</span>
            </div>
            <p class="admin-dashboard__muted">Заказы компаний по текущим назначениям. Если компания назначена нескольким менеджерам, её заказ учитывается каждому.</p>
            @forelse($dashboard['manager_ranking'] as $manager)
                <div class="admin-dashboard__manager">
                    <span class="admin-dashboard__place">{{ $manager['place'] }}</span>
                    <div class="admin-dashboard__manager-main">
                        @if($demoMode)<span>{{ $manager['name'] }}</span>@else<a href="{{ route('admin.truck-managers.edit', $manager['id']) }}">{{ $manager['name'] }}</a>@endif
                        <span>{{ $manager['orders'] }} заказов</span>
                    </div>
                    <strong>{{ number_format($manager['amount'], 2, ',', ' ') }} ₽</strong>
                </div>
            @empty
                <p class="admin-dashboard__empty">Менеджеры пока не созданы.</p>
            @endforelse
            <p class="admin-dashboard__footnote">Сумма — стоимость позиций заказов, не выручка.</p>
        </section>

        <section class="admin-dashboard__panel" aria-labelledby="dashboard-status-title">
            <div class="admin-dashboard__panel-heading">
                <div>
                    <p class="admin-dashboard__eyebrow">Заказы</p>
                    <h2 id="dashboard-status-title">Статусы заказов</h2>
                </div>
                <span class="admin-dashboard__tag">{{ $metrics['orders']['value'] }} всего</span>
            </div>
            <p class="admin-dashboard__muted">По заказам за период: «В оплате» — ожидают оплаты, «В доставке» — этапы Европы и России, «Выданные» — выполнены. Остальные статусы показаны отдельно.</p>
            @if($metrics['orders']['value'] === 0)
                <p class="admin-dashboard__empty">За выбранный период заказов нет.</p>
            @else
                <div class="admin-dashboard__status-list">
                    @foreach($dashboard['status_distribution'] as $status)
                        <div class="admin-dashboard__status-row" title="{{ $status['label'] }}: {{ $status['count'] }} ({{ number_format($status['percent'], 1, ',', ' ') }}%)" aria-label="{{ $status['label'] }}: {{ $status['count'] }}, {{ number_format($status['percent'], 1, ',', ' ') }} процента">
                            <div class="admin-dashboard__status-text"><span>{{ $status['label'] }}</span><strong>{{ $status['count'] }} · {{ number_format($status['percent'], 1, ',', ' ') }}%</strong></div>
                            <div class="admin-dashboard__status-track"><span class="admin-dashboard__status-fill admin-dashboard__status-fill--{{ $status['key'] }}" style="width: {{ $status['percent'] }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="admin-dashboard__panel" aria-labelledby="dashboard-recent-title">
        <div class="admin-dashboard__panel-heading">
            <div>
                <p class="admin-dashboard__eyebrow">Активность</p>
                <h2 id="dashboard-recent-title">Последние заказы за период</h2>
            </div>
            <a class="admin-dashboard__all-link" href="{{ route('admin.orders.index', ['tab' => 'company']) }}">Все заказы <span aria-hidden="true">→</span></a>
        </div>
        @if($dashboard['recent_orders'] === [])
            <p class="admin-dashboard__empty">За выбранный период заказов нет.</p>
        @else
            <div class="admin-dashboard__table-wrap">
                <table class="admin-dashboard__table">
                    <thead><tr><th>№ заказа</th><th>Компания</th><th>Менеджер</th><th>Сумма</th><th>Статус</th><th>Дата</th></tr></thead>
                    <tbody>
                        @foreach($dashboard['recent_orders'] as $order)
                            <tr>
                                <td>@if($demoMode){{ $order['number'] }}@else<a href="{{ route('admin.orders.show', $order['id']) }}">{{ $order['number'] }}</a>@endif</td>
                                <td>{{ $order['company'] }}</td>
                                <td>{{ $order['manager'] }}</td>
                                <td class="admin-dashboard__amount">{{ number_format($order['amount'], 2, ',', ' ') }} ₽</td>
                                <td><span class="admin-dashboard__order-status admin-dashboard__order-status--{{ $order['status_key'] }}">{{ $order['status'] }}</span></td>
                                <td>{{ $order['date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-dashboard.js') }}" defer></script>
@endpush
