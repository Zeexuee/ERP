<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_can_be_recorded_with_proof_pic_name_and_signature(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'PT Mitra Pembayaran',
            'email' => 'mitra@pembayaran.com',
            'phone' => '08123456789',
            'address' => 'Jl. Kebon Jeruk No. 8',
            'is_active' => true,
        ]);

        $salesOrder = SalesOrder::create([
            'customer_id' => $customer->id,
            'order_number' => 'SO-2026-8888',
            'status' => SalesOrderStatus::CONFIRMED,
            'total_amount' => 15000000,
        ]);

        $invoice = Invoice::create([
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-2026-8888',
            'status' => InvoiceStatus::UNPAID,
            'total_amount' => 15000000,
            'due_date' => now()->addDays(30),
        ]);

        $file = UploadedFile::fake()->create('bukti_transfer.pdf', 150, 'application/pdf');
        $signatureData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->post(route('invoices.payments.store', $invoice), [
            'amount' => 10000000,
            'payment_method' => 'Bank Transfer (BCA)',
            'payment_date' => now()->toDateString(),
            'pic_name' => 'Ahmad Faisal (Finance)',
            'proof_file' => $file,
            'signature' => $signatureData,
        ]);

        $response->assertSessionHas('success');

        $payment = Payment::first();
        $this->assertNotNull($payment);
        $this->assertEquals(10000000, $payment->amount);
        $this->assertEquals('Ahmad Faisal (Finance)', $payment->pic_name);
        $this->assertEquals($signatureData, $payment->signature);
        $this->assertNotNull($payment->proof_file);
        Storage::disk('public')->assertExists($payment->proof_file);

        $this->assertEquals(InvoiceStatus::PARTIAL, $invoice->fresh()->status);
    }
}
