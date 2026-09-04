<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExcelImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_export_and_import_work_properly(): void
    {
        // 1. Export
        Customer::create([
            'name' => 'PT Ekspor Sejahtera',
            'email' => 'ekspor@sejahtera.com',
            'phone' => '08123456789',
            'address' => 'Jakarta',
            'is_active' => true,
        ]);

        $exportResponse = $this->get(route('customers.export.excel'));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // 2. Template
        $templateResponse = $this->get(route('customers.template.excel'));
        $templateResponse->assertStatus(200);

        // 3. Import
        $csvContent = "nama_pelanggan,email,telepon,alamat,status_aktif\n";
        $csvContent .= "PT Import Pelanggan Baru,import@baru.com,08199887766,Surabaya,1\n";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csvContent);

        $importResponse = $this->post(route('customers.import.excel'), [
            'file' => $file,
        ]);

        $importResponse->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'PT Import Pelanggan Baru',
            'email' => 'import@baru.com',
        ]);
    }

    public function test_sales_order_export_and_import_work_properly(): void
    {
        // 1. Template
        $templateResponse = $this->get(route('sales-orders.template.excel'));
        $templateResponse->assertStatus(200);

        // 2. Import
        $csvContent = "nama_pelanggan,nama_produk,jumlah,satuan,harga_satuan,penerima_pesanan_pic\n";
        $csvContent .= "PT Mitra Industri Sukses,Biji Kopi Robusta Premium,25,kg,80000,Sales Admin Hendra\n";

        $file = UploadedFile::fake()->createWithContent('sales_orders.csv', $csvContent);

        $importResponse = $this->post(route('sales-orders.import.excel'), [
            'file' => $file,
        ]);

        $importResponse->assertRedirect(route('sales-orders.index'));

        $this->assertDatabaseHas('customers', ['name' => 'PT Mitra Industri Sukses']);
        $this->assertDatabaseHas('products', ['name' => 'Biji Kopi Robusta Premium']);

        $so = SalesOrder::where('pic_name', 'Sales Admin Hendra')->first();
        $this->assertNotNull($so);
        $this->assertEquals(2000000, $so->total_amount);
        $this->assertNotNull($so->productionRequest);
        $this->assertCount(1, $so->invoices);

        // 3. Export
        $exportResponse = $this->get(route('sales-orders.export.excel'));
        $exportResponse->assertStatus(200);
    }

    public function test_invoice_export_and_import_work_properly(): void
    {
        $customer = Customer::create([
            'name' => 'PT Pelanggan Faktur',
            'email' => 'faktur@pelanggan.com',
            'phone' => '0812334455',
            'address' => 'Bandung',
            'is_active' => true,
        ]);

        $so = SalesOrder::create([
            'customer_id' => $customer->id,
            'order_number' => 'SO-2026-TESTINV',
            'total_amount' => 1500000,
        ]);

        // 1. Template
        $templateResponse = $this->get(route('invoices.template.excel'));
        $templateResponse->assertStatus(200);

        // 2. Import
        $csvContent = "nomor_sales_order,total_tagihan,jatuh_tempo_yyyy_mm_dd,status_faktur\n";
        $csvContent .= "SO-2026-TESTINV,1500000,2026-10-15,unpaid\n";

        $file = UploadedFile::fake()->createWithContent('invoices.csv', $csvContent);

        $importResponse = $this->post(route('invoices.import.excel'), [
            'file' => $file,
        ]);

        $importResponse->assertRedirect(route('invoices.index'));
        $this->assertDatabaseHas('invoices', [
            'sales_order_id' => $so->id,
            'total_amount' => 1500000,
        ]);

        // 3. Export
        $exportResponse = $this->get(route('invoices.export.excel'));
        $exportResponse->assertStatus(200);
    }
}
