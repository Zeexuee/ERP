<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\ProductionRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductionRequest;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Admin / Sales user
        User::factory()->create([
            'name' => 'Sales Officer',
            'email' => 'sales@erp.com',
        ]);

        $this->call([
            ProductSeeder::class,
            CustomerSeeder::class,
        ]);

        $customer1 = Customer::first();
        $customer2 = Customer::skip(1)->first();
        $product1 = Product::where('sku', 'PRD-001')->first();
        $product3 = Product::where('sku', 'PRD-003')->first();

        // Sample Sales Order & Production Request & Invoice (Confirmed -> Processing -> Paid)
        $so = SalesOrder::create([
            'customer_id' => $customer2->id,
            'order_number' => 'SO-2026-0001',
            'status' => SalesOrderStatus::PROCESSING,
            'total_amount' => 47500000.00,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $product1->id,
            'quantity' => 3,
            'unit_price' => $product1->price,
            'subtotal' => $product1->price * 3,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $product3->id,
            'quantity' => 2,
            'unit_price' => $product3->price,
            'subtotal' => $product3->price * 2,
        ]);

        ProductionRequest::create([
            'sales_order_id' => $so->id,
            'request_number' => 'PR-2026-0001',
            'status' => ProductionRequestStatus::IN_PRODUCTION,
            'requested_date' => Carbon::now(),
        ]);

        $inv = Invoice::create([
            'sales_order_id' => $so->id,
            'invoice_number' => 'INV-2026-0001',
            'status' => InvoiceStatus::PARTIAL,
            'total_amount' => 47500000.00,
            'due_date' => Carbon::now()->addDays(30),
        ]);

        Payment::create([
            'invoice_id' => $inv->id,
            'payment_number' => 'PAY-2026-0001',
            'amount' => 20000000.00,
            'payment_method' => 'Bank Transfer (BCA)',
            'payment_date' => Carbon::now()->toDateString(),
        ]);
    }
}
