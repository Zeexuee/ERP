<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\ProductionRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductionRequest;
use App\Models\SalesOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'processing_orders' => SalesOrder::whereIn('status', [SalesOrderStatus::CONFIRMED, SalesOrderStatus::PROCESSING])->count(),
            'unpaid_invoices' => Invoice::whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL])->count(),
            'total_receivables' => Invoice::whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL])->sum('total_amount'),
            'total_revenue' => Payment::sum('amount'),
            'pending_production' => ProductionRequest::where('status', ProductionRequestStatus::PENDING)->count(),
        ];

        $recentOrders = SalesOrder::with('customer')->latest()->take(5)->get();
        $recentPayments = Payment::with('invoice.salesOrder.customer')->latest()->take(5)->get();
        $recentProductionRequests = ProductionRequest::with('salesOrder.customer')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentOrders', 'recentPayments', 'recentProductionRequests'));
    }
}
