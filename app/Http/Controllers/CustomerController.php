<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function show(Customer $customer)
    {
        $customer->load(['quotations', 'salesOrders']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui!');
    }

    public function destroy(Customer $customer)
    {
        // Check RESTRICT condition manually or catch query exception
        if ($customer->salesOrders()->exists() || $customer->quotations()->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi/penawaran!');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus (Soft Delete)!');
    }
}
