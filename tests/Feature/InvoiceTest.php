<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_show_page_renders_with_payments_proof_and_signature(): void
    {
        $customer = Customer::create([
            'name' => 'PT Invoice Test',
            'email' => 'invoice@test.com',
            'phone' => '08123456789',
            'address' => 'Jl. Gatot Subroto No. 1',
            'is_active' => true,
        ]);

        $salesOrder = SalesOrder::create([
            'customer_id' => $customer->id,
            'order_number' => 'SO-2026-7777',
            'status' => SalesOrderStatus::CONFIRMED,
            'total_amount' => 10000000,
        ]);

        $invoice = Invoice::create([
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-2026-7777',
            'status' => InvoiceStatus::PARTIAL,
            'total_amount' => 10000000,
            'due_date' => now()->addDays(14),
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_number' => 'PAY-2026-7777',
            'amount' => 5000000,
            'payment_method' => 'Bank Transfer (BCA)',
            'payment_date' => now()->toDateString(),
            'pic_name' => 'Bambang Sudiro',
            'proof_file' => 'payments/proofs/sample.pdf',
            'signature' => 'data:image/png;base64,sample',
        ]);

        $response = $this->get(route('invoices.show', $invoice));

        $response->assertStatus(200);
        $response->assertSee('INV-2026-7777');
        $response->assertSee('Bambang Sudiro');
        $response->assertSee('Lihat Bukti Bayar');
    }
}
