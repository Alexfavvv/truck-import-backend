<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class CustomerDashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('customer.search.index');
    }
}
