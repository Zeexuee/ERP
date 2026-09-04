<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_index_displays_purchase_history_and_remaining_invoices(): void
    {
        $customer = Customer::create([
            'name' => 'PT Sinar Abadi',
            'email' => 'sinar@abadi.com',
            'phone' => '08123456789',
            'address' => 'Jl. Sudirman No. 12',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'PRD-TEST-1',
            'name' => 'Mesin Industri Pro',
            'price' => 10000000,
            'stock_quantity' => 10,
        ]);

        $salesOrder = SalesOrder::create([
            'customer_id' => $customer->id,
            'order_number' => 'SO-2026-9999',
            'status' => SalesOrderStatus::PROCESSING,
            'total_amount' => 20000000,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10000000,
            'subtotal' => 20000000,
        ]);

        $invoice = Invoice::create([
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-2026-9999',
            'status' => InvoiceStatus::PARTIAL,
            'total_amount' => 20000000,
            'due_date' => now()->addDays(14),
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_number' => 'PAY-2026-9999',
            'amount' => 5000000,
            'payment_method' => 'Bank Transfer',
            'payment_date' => now()->toDateString(),
        ]);

        // Customer assertions
        $this->assertEquals(20000000, $customer->total_purchases_amount);
        $this->assertEquals(15000000, $customer->total_outstanding_balance);
        $this->assertEquals(15000000, $invoice->remaining_amount);

        // HTTP View assertion
        $response = $this->get(route('customers.index'));
        $response->assertStatus(200);
        $response->assertSee('PT Sinar Abadi');
        $response->assertSee('SO-2026-9999');
        $response->assertSee('INV-2026-9999');
        $response->assertSee('Mesin Industri Pro');
        $response->assertSee(number_format(15000000, 0, ',', '.'));
    }
}
