<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

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
}
