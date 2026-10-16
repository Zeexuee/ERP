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
     * Dashboard Utama Divisi STIN.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        $stats = [
            'total_sales_orders' => SalesOrder::count(),
            'total_customers' => Customer::count(),
            'total_products' => Product::count(),
            'total_production_requests' => ProductionRequest::count(),
        ];

        return view('stin.dashboard', compact('user', 'stats'));
    }
}
