<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\ProductionRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductionRequest;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan seluruh data sebelumnya
        Schema::disableForeignKeyConstraints();

        MaterialReceipt::truncate();
        Material::truncate();
        Payment::truncate();
        Invoice::truncate();
        ProductionRequest::truncate();
        SalesOrderItem::truncate();
        SalesOrder::truncate();
        ProductBranch::truncate();
        Product::truncate();
        Customer::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // Panggil seeder pengguna, produk gaharu sana'i, bahan baku, dan pembeli
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            MaterialSeeder::class,
            CustomerSeeder::class,
        ]);

        $custExport = Customer::where('email', 'procurement@oudnusantara.com')->first();
        $custMadinah = Customer::where('email', 'almadinah.gaharu@gmail.com')->first();
        $custDarussalam = Customer::where('email', 'pengadaan@darussalam-bogor.id')->first();

        $prodSuperMalaki = Product::where('sku', 'GHR-SNI-001')->first();
        $prodMaroke = Product::where('sku', 'GHR-SNI-002')->first();
        $prodKalbar = Product::where('sku', 'GHR-SNI-003')->first();
        $prodBukhoorChip = Product::where('sku', 'GHR-SNI-004')->first();
        $prodStick = Product::where('sku', 'GHR-SNI-005')->first();
        $prodMinyak = Product::where('sku', 'GHR-SNI-006')->first();

        // 1. Order 1: Pesanan Ekspor Gaharu Super Malaki & Minyak Gaharu (Status: Processing / In Production)
        $so1 = SalesOrder::create([
            'customer_id' => $custExport->id,
            'order_number' => 'SO-2026-0001',
            'status' => SalesOrderStatus::PROCESSING,
            'total_amount' => 53750000.00,
            'pic_name' => 'Ahmad Fauzi (Divisi Ekspor Gaharu)',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so1->id,
            'product_id' => $prodSuperMalaki->id,
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => $prodSuperMalaki->price,
            'subtotal' => $prodSuperMalaki->price * 10,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so1->id,
            'product_id' => $prodMinyak->id,
            'quantity' => 5,
            'unit' => 'tola',
            'unit_price' => $prodMinyak->price,
            'subtotal' => $prodMinyak->price * 5,
        ]);

        ProductionRequest::create([
            'sales_order_id' => $so1->id,
            'request_number' => 'PR-2026-0001',
            'status' => ProductionRequestStatus::IN_PRODUCTION,
            'requested_date' => Carbon::now()->subDays(2),
        ]);

        $inv1 = Invoice::create([
            'sales_order_id' => $so1->id,
            'invoice_number' => 'INV-2026-0001',
            'status' => InvoiceStatus::PARTIAL,
            'total_amount' => 53750000.00,
            'due_date' => Carbon::now()->addDays(20),
        ]);

        Payment::create([
            'invoice_id' => $inv1->id,
            'payment_number' => 'PAY-2026-0001',
            'amount' => 30000000.00,
            'payment_method' => 'Bank Transfer (Bank Mandiri)',
            'payment_date' => Carbon::now()->subDay()->toDateString(),
        ]);

        // 2. Order 2: Pasokan Grosir Toko Al-Madinah (Status: Confirmed / Pending Production)
        $so2 = SalesOrder::create([
            'customer_id' => $custMadinah->id,
            'order_number' => 'SO-2026-0002',
            'status' => SalesOrderStatus::CONFIRMED,
            'total_amount' => 18550000.00,
            'pic_name' => 'Siti Rahmah (Sales Counter Jkt)',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so2->id,
            'product_id' => $prodBukhoorChip->id,
            'quantity' => 20,
            'unit' => 'kotak',
            'unit_price' => $prodBukhoorChip->price,
            'subtotal' => $prodBukhoorChip->price * 20,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so2->id,
            'product_id' => $prodStick->id,
            'quantity' => 30,
            'unit' => 'pack',
            'unit_price' => $prodStick->price,
            'subtotal' => $prodStick->price * 30,
        ]);

        ProductionRequest::create([
            'sales_order_id' => $so2->id,
            'request_number' => 'PR-2026-0002',
            'status' => ProductionRequestStatus::PENDING,
            'requested_date' => Carbon::now()->subHours(6),
        ]);

        Invoice::create([
            'sales_order_id' => $so2->id,
            'invoice_number' => 'INV-2026-0002',
            'status' => InvoiceStatus::UNPAID,
            'total_amount' => 18550000.00,
            'due_date' => Carbon::now()->addDays(14),
        ]);

        // 3. Order 3: Pengadaan Acara Haul Akbar Pesantren Darussalam (Status: Completed & Paid)
        $so3 = SalesOrder::create([
            'customer_id' => $custDarussalam->id,
            'order_number' => 'SO-2026-0003',
            'status' => SalesOrderStatus::COMPLETED,
            'total_amount' => 29600000.00,
            'pic_name' => 'Ahmad Fauzi (Divisi Sales)',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so3->id,
            'product_id' => $prodMaroke->id,
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => $prodMaroke->price,
            'subtotal' => $prodMaroke->price * 5,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so3->id,
            'product_id' => $prodKalbar->id,
            'quantity' => 3,
            'unit' => 'kg',
            'unit_price' => $prodKalbar->price,
            'subtotal' => $prodKalbar->price * 3,
        ]);

        ProductionRequest::create([
            'sales_order_id' => $so3->id,
            'request_number' => 'PR-2026-0003',
            'status' => ProductionRequestStatus::FINISHED,
            'requested_date' => Carbon::now()->subDays(7),
        ]);

        $inv3 = Invoice::create([
            'sales_order_id' => $so3->id,
            'invoice_number' => 'INV-2026-0003',
            'status' => InvoiceStatus::PAID,
            'total_amount' => 29600000.00,
            'due_date' => Carbon::now()->addDays(7),
        ]);

        Payment::create([
            'invoice_id' => $inv3->id,
            'payment_number' => 'PAY-2026-0002',
            'amount' => 29600000.00,
            'payment_method' => 'Bank Transfer (BCA)',
            'payment_date' => Carbon::now()->subDays(3)->toDateString(),
        ]);

    }
}
