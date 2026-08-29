<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Exception;

class PaymentController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        try {
            $payment = $this->invoiceService->recordPayment($invoice, $request->validated());

            return back()->with('success', "Pembayaran {$payment->payment_number} sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
