<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\ExcelImportExportService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with([
            'salesOrders' => function ($query) {
                $query->latest();
            },
            'salesOrders.items.product',
            'salesOrders.invoices.payments',
        ])->latest()->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil ditambahkan!',
                'customer' => $customer,
            ], 201);
        }

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function show(Customer $customer)
    {
        $customer->load(['salesOrders']);

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
        if ($customer->salesOrders()->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi!');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus (Soft Delete)!');
    }

    public function exportExcel(ExcelImportExportService $excelService)
    {
        return $excelService->exportCustomers();
    }

    public function templateExcel(ExcelImportExportService $excelService)
    {
        return $excelService->downloadCustomerTemplate();
    }

    public function importExcel(Request $request, ExcelImportExportService $excelService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $result = $excelService->importCustomers($request->file('file'));

        if ($result['success'] > 0) {
            $msg = "Berhasil mengimpor {$result['success']} pelanggan!";
            if (! empty($result['errors'])) {
                $msg .= ' Beberapa baris dilewati karena data tidak valid: '.implode(' ', array_slice($result['errors'], 0, 3));
            }

            return redirect()->route('customers.index')->with('success', $msg);
        }

        return redirect()->route('customers.index')->with('error', 'Gagal mengimpor pelanggan: '.implode(' | ', $result['errors']));
    }
}
