<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductionRequest;
use App\Models\SalesOrder;
use Illuminate\View\View;

class StinController extends Controller
{
    /**
     * Dashboard Khusus Divisi STIN.
     */
    public function dashboard(): View
    {
        $metrics = [
            'total_customers' => Customer::count(),
            'total_sales_orders' => SalesOrder::count(),
            'total_production_requests' => ProductionRequest::count(),
            'total_products' => Product::count(),
        ];

        $recentAudits = SalesOrder::with(['customer', 'items.product'])->latest()->take(5)->get();

        return view('stin.dashboard', compact('metrics', 'recentAudits'));
    }
}
