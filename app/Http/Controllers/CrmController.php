<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\View\View;

class CrmController extends Controller
{
    /**
     * Dashboard Divisi Manajemen CRM.
     */
    public function dashboard(): View
    {
        $customers = Customer::withCount('salesOrders')->latest()->paginate(10);
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $topSpenders = Customer::with(['salesOrders'])->get()->sortByDesc(function ($c) {
            return $c->salesOrders->sum('total_amount');
        })->take(5);

        return view('crm.dashboard', compact('customers', 'totalCustomers', 'activeCustomers', 'topSpenders'));
    }
}
