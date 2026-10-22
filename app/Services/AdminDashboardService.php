<?php

namespace App\Services;

use App\Enums\TruckInvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public const PERIODS = [
        'this_month' => 'Этот месяц',
        'last_month' => 'Прошлый месяц',
        'last_3_months' => 'Последние 3 месяца',
        'last_6_months' => 'Последние 6 месяцев',
        'this_year' => 'Этот год',
    ];

    private const ORDER_STATUS_LABELS = [
        'created' => 'Создан',
        'unpaid' => 'Ожидает оплаты',
        'paid' => 'Оплачен',
        'arrived_europe' => 'Прибыл в Европу',
        'shipped_russia' => 'Отправлен в Россию',
        'arrived_russia' => 'Прибыл в Россию',
        'completed' => 'Выполнен',
        'cancelled' => 'Отменён',
    ];

    private const STATUS_GROUPS = [
        'payment' => ['label' => 'В оплате', 'statuses' => ['unpaid']],
        'delivery' => ['label' => 'В доставке', 'statuses' => ['arrived_europe', 'shipped_russia', 'arrived_russia']],
        'issued' => ['label' => 'Выданные', 'statuses' => ['completed']],
        'created' => ['label' => 'Созданы', 'statuses' => ['created']],
        'paid' => ['label' => 'Оплачены', 'statuses' => ['paid']],
        'cancelled' => ['label' => 'Отменены', 'statuses' => ['cancelled']],
    ];

    public function build(string $periodKey): array
    {
        $period = $this->period($periodKey);
        $start = $period['start'];
        $end = $period['end'];
        $previousStart = $period['previous_start'];
        $previousEnd = $period['previous_end'];

        $orders = $this->ordersIn($start, $end)->count();
        $previousOrders = $this->ordersIn($previousStart, $previousEnd)->count();
        $revenue = (float) $this->paidInvoicesIn($start, $end)->sum('amount');
        $previousRevenue = (float) $this->paidInvoicesIn($previousStart, $previousEnd)->sum('amount');
        $paidInvoices = $this->paidInvoicesIn($start, $end)->count();
        $previousPaidInvoices = $this->paidInvoicesIn($previousStart, $previousEnd)->count();
        $clients = $this->newCompaniesIn($start, $end)->count();
        $previousClients = $this->newCompaniesIn($previousStart, $previousEnd)->count();
        $monthlyAverage = $this->monthlyOrderAverage($period['current_month_start']);

        return [
            'period' => $period,
            'periods' => self::PERIODS,
            'metrics' => [
                'orders' => $this->metric($orders, $previousOrders),
                'revenue' => $this->metric($revenue, $previousRevenue),
                'clients' => $this->metric($clients, $previousClients),
                'paid_invoices' => $this->metric($paidInvoices, $previousPaidInvoices),
            ],
            'monthly_average' => $monthlyAverage,
            'monthly_average_change' => $monthlyAverage > 0
                ? round((($orders / $period['month_count']) / $monthlyAverage - 1) * 100, 1)
                : null,
            'status_distribution' => $this->statusDistribution($start, $end, $orders),
            'manager_ranking' => $this->managerRanking($start, $end),
            'chart' => $this->chart($period),
            'recent_orders' => $this->recentOrders($start, $end),
        ];
    }

    private function period(string $key): array
    {
        $now = CarbonImmutable::now(config('app.timezone'));
        $monthStart = $now->startOfMonth();

        [$start, $end, $previousStart, $previousEnd, $daily] = match ($key) {
            'last_month' => [
                $monthStart->subMonth(), $monthStart,
                $monthStart->subMonths(2), $monthStart->subMonth(), true,
            ],
            'last_3_months' => [
                $monthStart->subMonths(2), $monthStart->addMonth(),
                $monthStart->subMonths(5), $monthStart->subMonths(2), false,
            ],
            'last_6_months' => [
                $monthStart->subMonths(5), $monthStart->addMonth(),
                $monthStart->subMonths(11), $monthStart->subMonths(5), false,
            ],
            'this_year' => [
                $now->startOfYear(), $monthStart->addMonth(),
                $now->startOfYear()->subYear(), $monthStart->addMonth()->subYear(), false,
            ],
            default => [
                $monthStart, $monthStart->addMonth(),
                $monthStart->subMonth(), $monthStart, true,
            ],
        };

        return [
            'key' => $key,
            'label' => self::PERIODS[$key],
            'start' => $start,
            'end' => $end,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
            'current_month_start' => $monthStart,
            'month_count' => max(1, (int) $start->diffInMonths($end)),
            'daily' => $daily,
            'comparison_label' => $key === 'this_month' ? 'к прошлому месяцу' : 'к предыдущему периоду',
            'range_label' => $start->format('d.m.Y').' — '.$end->subDay()->format('d.m.Y'),
        ];
    }

    private function ordersIn(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return Order::query()->where('created_at', '>=', $start)->where('created_at', '<', $end);
    }

    private function paidInvoicesIn(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return Invoice::query()
            ->where('truck_status', TruckInvoiceStatus::Paid->value)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<', $end);
    }

    private function newCompaniesIn(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return User::query()
            ->where('role', UserRole::AdminCustomer->value)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);
    }

    private function metric(int|float $value, int|float $previous): array
    {
        return [
            'value' => $value,
            'previous' => $previous,
            'difference' => round($value - $previous, 2),
            'change' => $previous > 0 ? round(($value / $previous - 1) * 100, 1) : null,
        ];
    }

    private function monthlyOrderAverage(CarbonImmutable $currentMonthStart): ?float
    {
        $firstOrder = Order::query()->where('created_at', '<', $currentMonthStart)->min('created_at');
        if ($firstOrder === null) {
            return null;
        }

        $firstMonth = CarbonImmutable::parse($firstOrder, config('app.timezone'))->startOfMonth();
        $monthCount = (int) $firstMonth->diffInMonths($currentMonthStart);
        if ($monthCount === 0) {
            return null;
        }

        $orderCount = $this->ordersIn($firstMonth, $currentMonthStart)->count();

        return round($orderCount / $monthCount, 1);
    }

    private function statusDistribution(CarbonImmutable $start, CarbonImmutable $end, int $orderCount): array
    {
        $counts = $this->ordersIn($start, $end)
            ->select('status')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->getRawOriginal('status') => (int) $row->total]);

        $rows = [];
        foreach (self::STATUS_GROUPS as $key => $group) {
            $count = array_sum(array_map(fn ($status) => $counts->get($status, 0), $group['statuses']));
            $rows[] = [
                'key' => $key,
                'label' => $group['label'],
                'count' => $count,
                'percent' => $orderCount > 0 ? round($count / $orderCount * 100, 1) : 0,
            ];
        }

        return $rows;
    }

    private function managerRanking(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $orderTotals = DB::table('order_items as items')
            ->join('orders as source_orders', 'source_orders.id', '=', 'items.order_id')
            ->whereNull('source_orders.deleted_at')
            ->where('source_orders.created_at', '>=', $start)
            ->where('source_orders.created_at', '<', $end)
            ->select('items.order_id')
            ->selectRaw('SUM(items.price * items.quantity) AS total')
            ->groupBy('items.order_id');

        $rows = DB::table('users as managers')
            ->leftJoin('truck_manager_company as assignments', 'assignments.manager_id', '=', 'managers.id')
            ->leftJoin('orders as orders', function (JoinClause $join) use ($start, $end) {
                $join->on('orders.company_admin_id', '=', 'assignments.company_admin_id')
                    ->whereNull('orders.deleted_at')
                    ->where('orders.created_at', '>=', $start)
                    ->where('orders.created_at', '<', $end);
            })
            ->leftJoinSub($orderTotals, 'order_totals', 'order_totals.order_id', '=', 'orders.id')
            ->where('managers.role', UserRole::ManagerTruck->value)
            ->select('managers.id', 'managers.full_name', 'managers.name')
            ->selectRaw('COUNT(orders.id) AS orders_count')
            ->selectRaw('COALESCE(SUM(order_totals.total), 0) AS orders_amount')
            ->groupBy('managers.id', 'managers.full_name', 'managers.name')
            ->orderByDesc('orders_count')
            ->orderBy('managers.id')
            ->limit(5)
            ->get();

        return $rows->map(fn ($row, $index) => [
            'place' => $index + 1,
            'id' => $row->id,
            'name' => $row->full_name ?: $row->name,
            'orders' => (int) $row->orders_count,
            'amount' => (float) $row->orders_amount,
        ])->all();
    }

    private function chart(array $period): array
    {
        $start = $period['start'];
        $end = $period['end'];
        $daily = $period['daily'];

        $ordersByBucket = [];
        foreach ($this->ordersIn($start, $end)
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS total')
            ->groupByRaw('DATE(created_at)')
            ->get() as $row) {
            $key = $daily ? $row->day : substr($row->day, 0, 7);
            $ordersByBucket[$key] = ($ordersByBucket[$key] ?? 0) + (int) $row->total;
        }

        $revenueByBucket = $paidInvoicesByBucket = [];
        foreach ($this->paidInvoicesIn($start, $end)
            ->selectRaw('DATE(paid_at) AS day, SUM(amount) AS total, COUNT(*) AS invoice_count')
            ->groupByRaw('DATE(paid_at)')
            ->get() as $row) {
            $key = $daily ? $row->day : substr($row->day, 0, 7);
            $revenueByBucket[$key] = ($revenueByBucket[$key] ?? 0) + (float) $row->total;
            $paidInvoicesByBucket[$key] = ($paidInvoicesByBucket[$key] ?? 0) + (int) $row->invoice_count;
        }

        $clientsByBucket = [];
        foreach ($this->newCompaniesIn($start, $end)
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS total')
            ->groupByRaw('DATE(created_at)')
            ->get() as $row) {
            $key = $daily ? $row->day : substr($row->day, 0, 7);
            $clientsByBucket[$key] = ($clientsByBucket[$key] ?? 0) + (int) $row->total;
        }

        $labels = $orderValues = $revenueValues = $clientValues = $paidInvoiceValues = [];
        $cursor = $start;
        while ($cursor->lessThan($end)) {
            $key = $cursor->format($daily ? 'Y-m-d' : 'Y-m');
            $labels[] = $cursor->format($daily ? 'd.m' : 'm.Y');
            $orderValues[] = $ordersByBucket[$key] ?? 0;
            $revenueValues[] = round($revenueByBucket[$key] ?? 0, 2);
            $clientValues[] = $clientsByBucket[$key] ?? 0;
            $paidInvoiceValues[] = $paidInvoicesByBucket[$key] ?? 0;
            $cursor = $daily ? $cursor->addDay() : $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'orders' => $orderValues,
            'revenue' => $revenueValues,
            'clients' => $clientValues,
            'paid_invoices' => $paidInvoiceValues,
        ];
    }

    private function recentOrders(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $orders = $this->ordersIn($start, $end)
            ->with(['companyAdmin:id,company,full_name,name', 'items:id,order_id,price,quantity'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $companyIds = $orders->pluck('company_admin_id')->filter()->unique();
        $managerNames = $companyIds->isEmpty() ? collect() : DB::table('truck_manager_company as assignments')
            ->join('users as managers', 'managers.id', '=', 'assignments.manager_id')
            ->where('managers.role', UserRole::ManagerTruck->value)
            ->whereIn('assignments.company_admin_id', $companyIds)
            ->select('assignments.company_admin_id', 'managers.full_name', 'managers.name')
            ->get()
            ->groupBy('company_admin_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => $row->full_name ?: $row->name)->implode(', '));

        return $orders->map(fn (Order $order) => [
            'id' => $order->id,
            'number' => $order->order_number,
            'company' => $order->company_admin_id === null
                ? 'Трак Импорт'
                : ($order->companyAdmin?->company ?: ($order->companyAdmin?->full_name ?: 'Компания недоступна')),
            'manager' => $managerNames->get($order->company_admin_id, '—'),
            'amount' => $order->items->sum(fn ($item) => $item->price * $item->quantity),
            'status' => self::ORDER_STATUS_LABELS[$order->status->value] ?? $order->status->label(),
            'status_key' => $order->status->value,
            'date' => $order->created_at->format('d.m.Y H:i'),
        ])->all();
    }
}
