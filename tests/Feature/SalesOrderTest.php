<?php

namespace Tests\Feature;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_order_show_page_loads_successfully(): void
    {
        $customer = Customer::create([
            'name' => 'PT Pelanggan Sukses',
            'email' => 'sukses@perusahaan.com',
            'phone' => '081122334455',
            'address' => 'Jl. Thamrin No. 5',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'PRD-SO-TEST',
            'name' => 'Produk Tes SO',
            'price' => 5000000,
            'stock_quantity' => 15,
            'unit' => 'kg',
        ]);

        $salesOrder = SalesOrder::create([
            'customer_id' => $customer->id,
            'order_number' => 'SO-2026-0001',
            'status' => SalesOrderStatus::CONFIRMED,
            'total_amount' => 10000000,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit' => 'kg',
            'unit_price' => 5000000,
            'subtotal' => 10000000,
        ]);

        $response = $this->get(route('sales-orders.show', $salesOrder));

        $response->assertStatus(200);
        $response->assertSee('SO-2026-0001');
        $response->assertSee('PT Pelanggan Sukses');
        $response->assertSee('Produk Tes SO');
        $response->assertSee('2 kg');
    }

    public function test_sales_order_can_be_created_with_custom_units(): void
    {
        $customer = Customer::create([
            'name' => 'PT Komoditas Sejahtera',
            'email' => 'komoditas@sejahtera.com',
            'phone' => '081987654321',
            'address' => 'Jl. Pergudangan No. 12',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'PRD-BIJI-KOPI',
            'name' => 'Biji Kopi Arabika Grade A',
            'price' => 120000,
            'stock_quantity' => 500,
            'unit' => 'kg',
        ]);

        $response = $this->post(route('sales-orders.store'), [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 25.5,
                    'unit' => 'kg',
                    'unit_price' => 120000,
                ],
            ],
        ]);

        $response->assertRedirect();

        $so = SalesOrder::first();
        $this->assertNotNull($so);
        $this->assertEquals(3060000, $so->total_amount);

        $item = $so->items->first();
        $this->assertEquals('kg', $item->unit);
        $this->assertEquals(25.5, $item->quantity);
    }

    public function test_sales_order_can_be_created_with_pic_and_signature(): void
    {
        $customer = Customer::create([
            'name' => 'PT Manufaktur Unggul',
            'email' => 'unggul@manufaktur.com',
            'phone' => '081233445566',
            'address' => 'Kawasan Industri MM2100',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'PRD-BAHAN-A',
            'name' => 'Bahan Baku Grade A',
            'price' => 50000,
            'stock_quantity' => 100,
            'unit' => 'kg',
        ]);

        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->post(route('sales-orders.store'), [
            'customer_id' => $customer->id,
            'pic_name' => 'Budi Santoso (Head of Sales)',
            'signature' => $fakeSignature,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit' => 'kg',
                    'unit_price' => 50000,
                ],
            ],
        ]);

        $response->assertRedirect();

        $so = SalesOrder::where('pic_name', 'Budi Santoso (Head of Sales)')->first();
        $this->assertNotNull($so);
        $this->assertEquals($fakeSignature, $so->signature);
        $this->assertNotNull($so->productionRequest);
        $this->assertCount(1, $so->invoices);
        $this->assertEquals(500000, $so->invoices->first()->total_amount);

        $showResponse = $this->get(route('sales-orders.show', $so));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Budi Santoso (Head of Sales)');
        $showResponse->assertSee('Tanda Tangan Digital');
        $showResponse->assertSee($so->productionRequest->request_number);
        $showResponse->assertSee($so->invoices->first()->invoice_number);
    }
}
