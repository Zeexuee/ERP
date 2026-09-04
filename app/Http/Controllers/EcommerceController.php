<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\View\View;

class EcommerceController extends Controller
{
    /**
     * Dashboard Divisi Manajemen E-Commerce.
     */
    public function dashboard(): View
    {
        $orders = SalesOrder::with(['customer', 'items.product'])->latest()->take(10)->get();
        $products = Product::where('stock_quantity', '>', 0)->latest()->take(8)->get();
        $totalSales = SalesOrder::sum('total_amount');
        $orderCount = SalesOrder::count();

        return view('ecommerce.dashboard', compact('orders', 'products', 'totalSales', 'orderCount'));
    }
}
