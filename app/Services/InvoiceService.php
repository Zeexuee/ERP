<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    /**
     * Record a payment for an invoice inside a DB transaction.
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        if ($invoice->status === InvoiceStatus::PAID) {
            throw new InvalidArgumentException("Invoice {$invoice->invoice_number} is already fully paid.");
        }

        if ($invoice->status === InvoiceStatus::CANCELLED) {
            throw new InvalidArgumentException("Cannot record payment for a cancelled invoice.");
        }

        return DB::transaction(function () use ($invoice, $data) {
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_number' => $paymentNumber,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'] ?? Carbon::now()->toDateString(),
            ]);

            // Calculate total paid amount
            $totalPaid = $invoice->payments()->sum('amount');

            if ($totalPaid >= $invoice->total_amount) {
                $invoice->update(['status' => InvoiceStatus::PAID]);
            } else if ($totalPaid > 0) {
                $invoice->update(['status' => InvoiceStatus::PARTIAL]);
            }

            return $payment;
        });
    }
}
