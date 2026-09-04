<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\ExcelImportExportService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['salesOrder.customer', 'payments'])->latest()->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['salesOrder.customer', 'salesOrder.items.product', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    public function exportExcel(ExcelImportExportService $excelService)
    {
        return $excelService->exportInvoices();
    }

    public function templateExcel(ExcelImportExportService $excelService)
    {
        return $excelService->downloadInvoiceTemplate();
    }

    public function importExcel(Request $request, ExcelImportExportService $excelService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $result = $excelService->importInvoices($request->file('file'));

        if ($result['success'] > 0) {
            $msg = "Berhasil mengimpor {$result['success']} Faktur Invoice!";
            if (! empty($result['errors'])) {
                $msg .= ' Beberapa baris dilewati karena data tidak valid: '.implode(' ', array_slice($result['errors'], 0, 3));
            }

            return redirect()->route('invoices.index')->with('success', $msg);
        }

        return redirect()->route('invoices.index')->with('error', 'Gagal mengimpor Faktur Invoice: '.implode(' | ', $result['errors']));
    }
}
