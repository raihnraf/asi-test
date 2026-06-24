<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'totalRevenue' => (float) SalesOrder::query()->sum('total_price'),
            'totalProducts' => Product::query()->count(),
            'totalSalesOrders' => SalesOrder::query()->count(),
        ]);
    }
}
