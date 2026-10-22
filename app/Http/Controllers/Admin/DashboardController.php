<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AdminDashboardService $dashboard): View
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(array_keys(AdminDashboardService::PERIODS))],
        ]);

        $data = $dashboard->build($filters['period'] ?? 'this_month');
        $demoMode = app()->environment(['local', 'staging']) && $request->boolean('demo');

        if ($demoMode) {
            $data = $this->withDemoData($data);
        }

        return view('admin.dashboard.index', [
            'dashboard' => $data,
            'demoMode' => $demoMode,
        ]);
    }

    private function withDemoData(array $dashboard): array
    {
        $dashboard['metrics'] = [
            'orders' => ['value' => 62, 'previous' => 48, 'difference' => 14, 'change' => 29.2],
            'revenue' => ['value' => 3840000, 'previous' => 3210000, 'difference' => 630000, 'change' => 19.6],
            'clients' => ['value' => 8, 'previous' => 5, 'difference' => 3, 'change' => 60.0],
            'paid_invoices' => ['value' => 21, 'previous' => 16, 'difference' => 5, 'change' => 31.3],
        ];
        $dashboard['monthly_average'] = 43.6;
        $dashboard['monthly_average_change'] = 42.2;

        $dashboard['status_distribution'] = [
            ['key' => 'payment', 'label' => 'В оплате', 'count' => 9, 'percent' => 14.5],
            ['key' => 'delivery', 'label' => 'В доставке', 'count' => 20, 'percent' => 32.3],
            ['key' => 'issued', 'label' => 'Выданные', 'count' => 21, 'percent' => 33.9],
            ['key' => 'created', 'label' => 'Созданы', 'count' => 4, 'percent' => 6.5],
            ['key' => 'paid', 'label' => 'Оплачены', 'count' => 6, 'percent' => 9.7],
            ['key' => 'cancelled', 'label' => 'Отменены', 'count' => 2, 'percent' => 3.2],
        ];
        $dashboard['manager_ranking'] = [
            ['place' => 1, 'id' => null, 'name' => 'Александр Смирнов', 'orders' => 22, 'amount' => 1284600.00],
            ['place' => 2, 'id' => null, 'name' => 'Елена Кузнецова', 'orders' => 18, 'amount' => 946250.00],
            ['place' => 3, 'id' => null, 'name' => 'Дмитрий Волков', 'orders' => 14, 'amount' => 782900.00],
            ['place' => 4, 'id' => null, 'name' => 'Ирина Соколова', 'orders' => 11, 'amount' => 615400.00],
            ['place' => 5, 'id' => null, 'name' => 'Максим Орлов', 'orders' => 8, 'amount' => 438750.00],
        ];

        $dashboard['recent_orders'] = [
            ['id' => null, 'number' => 'ORD-2026-00062', 'company' => 'ООО «СеверТрак»', 'manager' => 'Александр Смирнов', 'amount' => 184500.00, 'status' => 'Отправлен в Россию', 'status_key' => 'shipped_russia', 'date' => '22.10.2026 11:42'],
            ['id' => null, 'number' => 'ORD-2026-00061', 'company' => 'ООО «АвтоПрофи»', 'manager' => 'Елена Кузнецова', 'amount' => 92680.00, 'status' => 'Выполнен', 'status_key' => 'completed', 'date' => '22.10.2026 10:18'],
            ['id' => null, 'number' => 'ORD-2026-00060', 'company' => 'ООО «ТрансСервис»', 'manager' => 'Дмитрий Волков', 'amount' => 347250.00, 'status' => 'Ожидает оплаты', 'status_key' => 'unpaid', 'date' => '21.10.2026 16:07'],
            ['id' => null, 'number' => 'ORD-2026-00059', 'company' => 'ООО «СеверТрак»', 'manager' => 'Александр Смирнов', 'amount' => 128940.00, 'status' => 'Прибыл в Россию', 'status_key' => 'arrived_russia', 'date' => '21.10.2026 14:35'],
            ['id' => null, 'number' => 'ORD-2026-00058', 'company' => 'ООО «Магистраль»', 'manager' => 'Ирина Соколова', 'amount' => 76400.00, 'status' => 'Оплачен', 'status_key' => 'paid', 'date' => '21.10.2026 09:26'],
        ];

        $chartLength = count($dashboard['chart']['labels']);
        $dashboard['chart']['orders'] = $this->weightedSeries($chartLength, 62, 0.7);
        $dashboard['chart']['revenue'] = $this->weightedSeries($chartLength, 3840000, 0.8);
        $dashboard['chart']['clients'] = $this->weightedSeries($chartLength, 8, 0.4);
        $dashboard['chart']['paid_invoices'] = $this->weightedSeries($chartLength, 21, 0.5);

        return $dashboard;
    }

    private function weightedSeries(int $length, int $total, float $variation): array
    {
        if ($length < 1) {
            return [];
        }

        $weights = [];
        for ($index = 0; $index < $length; $index++) {
            $weights[] = max(.15, 1 + $variation * sin(($index + 1) * 1.37) + .28 * cos(($index + 2) * .53));
        }

        $weightTotal = array_sum($weights);
        $values = array_map(fn ($weight) => (int) floor($total * $weight / $weightTotal), $weights);
        $remainder = $total - array_sum($values);
        for ($index = 0; $index < $remainder; $index++) {
            $values[$index % $length]++;
        }

        return $values;
    }
}
