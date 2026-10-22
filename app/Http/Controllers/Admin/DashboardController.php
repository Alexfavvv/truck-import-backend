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

        return view('admin.dashboard.index', [
            'dashboard' => $dashboard->build($filters['period'] ?? 'this_month'),
        ]);
    }
}
