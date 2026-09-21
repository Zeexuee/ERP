<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\MaterialSortBatch;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManufacturingPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected User $productionUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productionUser = User::factory()->create([
            'role' => UserRole::PRODUCTION,
        ]);
    }

    public function test_can_view_sorting_pages(): void
    {
        $response = $this->actingAs($this->productionUser)->get(route('production.sorts.index'));
        $response->assertOk();
        $response->assertSee('Warehouse');
        $response->assertSee('Sortir Kayu');
        $response->assertSee('Barang Gudang');
        $response->assertSee('Barang Masuk');

        $createResponse = $this->actingAs($this->productionUser)->get(route('production.sorts.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Warehouse');
    }

    public function test_can_process_independent_wood_sorting_workflow(): void
    {
        // 1. Setup Bahan Baku Sesuai Skenario User
        $kcb = Material::create([
            'code' => 'MAT-RAW-KCB',
            'name' => 'Kayu Mentah KCB',
            'category' => 'Kayu Mentah',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'unit_cost' => 150000,
        ]);

        $sp = Material::create([
            'code' => 'MAT-TBK-SP',
            'name' => 'Kayu Tembak Grade SP',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 10.00,
            'unit_cost' => 250000,
        ]);

        $miniSp = Material::create([
            'code' => 'MAT-TBK-MINI-SP',
            'name' => 'Kayu Tembak Mini SP',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 5.00,
            'unit_cost' => 200000,
        ]);

        $alami = Material::create([
            'code' => 'MAT-TBK-ALAMI',
            'name' => 'Kayu Tembak Alami',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 15.00,
            'unit_cost' => 180000,
        ]);

        $miniAlami = Material::create([
            'code' => 'MAT-TBK-MINI-ALAMI',
            'name' => 'Kayu Tembak Mini Alami',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 8.00,
            'unit_cost' => 160000,
        ]);

        $afkir = Material::create([
            'code' => 'MAT-AFKIR-01',
            'name' => 'Kayu Afkir',
            'category' => 'Afkir & Sisa',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'unit_cost' => 50000,
        ]);

        // 2. Submit Sortir: Beli KCB 20 kg -> Jemur 18 kg (susut 2 kg) -> SP 7kg, Mini SP 2kg, Alami 4kg, Mini Alami 3kg, Afkir 2kg (Total 18 kg)
        $payload = [
            'source_material_id' => $kcb->id,
            'initial_weight' => 20.00,
            'dried_weight' => 18.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Mandor Bambang',
            'notes' => 'Sortir kayu KCB pengeringan 2 hari.',
            'items' => [
                ['target_material_id' => $sp->id, 'result_weight' => 7.00, 'notes' => 'Kualitas Prima'],
                ['target_material_id' => $miniSp->id, 'result_weight' => 2.00, 'notes' => 'Potongan Kecil SP'],
                ['target_material_id' => $alami->id, 'result_weight' => 4.00, 'notes' => 'Alami Bagus'],
                ['target_material_id' => $miniAlami->id, 'result_weight' => 3.00, 'notes' => 'Mini Alami'],
                ['target_material_id' => $afkir->id, 'result_weight' => 2.00, 'notes' => 'Afkir simpan gudang'],
            ],
        ];

        $response = $this->actingAs($this->productionUser)->post(route('production.sorts.store'), $payload);
        $response->assertRedirect();

        // 3. Verifikasi Mutasi Stok di Gudang
        $this->assertEquals(30.00, $kcb->fresh()->stock_quantity); // 50 - 20 = 30
        $this->assertEquals(17.00, $sp->fresh()->stock_quantity);  // 10 + 7 = 17
        $this->assertEquals(7.00, $miniSp->fresh()->stock_quantity); // 5 + 2 = 7
        $this->assertEquals(19.00, $alami->fresh()->stock_quantity); // 15 + 4 = 19
        $this->assertEquals(11.00, $miniAlami->fresh()->stock_quantity); // 8 + 3 = 11
        $this->assertEquals(2.00, $afkir->fresh()->stock_quantity); // 0 + 2 = 2

        // 4. Verifikasi Database Batch Sortir
        $this->assertDatabaseHas('material_sort_batches', [
            'source_material_id' => $kcb->id,
            'initial_weight' => 20.00,
            'dried_weight' => 18.00,
            'drying_loss_weight' => 2.00,
            'pic_name' => 'Mandor Bambang',
        ]);
    }

    public function test_can_initiate_sorting_and_submit_sorting_report_separately(): void
    {
        $raw = Material::create([
            'code' => 'MAT-RAW-002',
            'name' => 'Kayu Mentah Albasia',
            'category' => 'Kayu Mentah',
            'unit' => 'kg',
            'stock_quantity' => 40.00,
            'unit_cost' => 100000,
        ]);

        $target = Material::create([
            'code' => 'MAT-TBK-002',
            'name' => 'Kayu Tembak Albasia Super',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 5.00,
            'unit_cost' => 200000,
        ]);

        // Tahap 1: Inisiasi proses sortir di /production/sorts/create
        $createResponse = $this->actingAs($this->productionUser)->get(route('production.sorts.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('data-stock="40"', false);
        $createResponse->assertSee('stockDisplayCard');

        $initPayload = [
            'source_material_id' => $raw->id,
            'initial_weight' => 25.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Petugas Jemur',
            'notes' => 'Inisiasi jemur bahan albasia.',
        ];

        $initResponse = $this->actingAs($this->productionUser)->post(route('production.sorts.store'), $initPayload);
        $initResponse->assertRedirect();

        // Verifikasi stok mentah terpotong dan status in_progress
        $this->assertEquals(15.00, $raw->fresh()->stock_quantity); // 40 - 25 = 15
        $sortBatch = MaterialSortBatch::where('source_material_id', $raw->id)->first();
        $this->assertNotNull($sortBatch);
        $this->assertEquals('in_progress', $sortBatch->status);
        $this->assertNull($sortBatch->dried_weight);

        // Halaman detail menampilkan status 'Sedang Disortir' dan form laporan
        $showResponse = $this->actingAs($this->productionUser)->get(route('production.sorts.show', $sortBatch));
        $showResponse->assertOk();
        $showResponse->assertSee('Sedang Disortir');
        $showResponse->assertDontSee('Dalam Proses Jemur');
        $showResponse->assertSee('Laporan Hasil Sortir');

        // Halaman index juga menampilkan status 'Sedang Disortir'
        $indexResponse = $this->actingAs($this->productionUser)->get(route('production.sorts.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Sedang Disortir');
        $indexResponse->assertDontSee('Sedang Jemur');

        // Tahap 2: Input Laporan Hasil Sortir (Report Sorting)
        $reportPayload = [
            'dried_weight' => 23.00,
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Mandor Penimbang',
            'items' => [
                ['target_material_id' => $target->id, 'result_weight' => 23.00, 'notes' => 'Grade A'],
            ],
        ];

        $reportResponse = $this->actingAs($this->productionUser)->post(route('production.sorts.store-report', $sortBatch), $reportPayload);
        $reportResponse->assertRedirect();

        // Verifikasi batch selesai & stok target bertambah
        $sortBatch->refresh();
        $this->assertEquals('completed', $sortBatch->status);
        $this->assertEquals(23.00, $sortBatch->dried_weight);
        $this->assertEquals(2.00, $sortBatch->drying_loss_weight); // 25 - 23 = 2
        $this->assertEquals(28.00, $target->fresh()->stock_quantity); // 5 + 23 = 28
    }

    public function test_sort_report_fails_if_target_material_has_different_unit_than_source(): void
    {
        $raw = Material::create([
            'code' => 'MAT-RAW-UNIT',
            'name' => 'Kayu Mentah Unit Test',
            'category' => 'Kayu Mentah',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
        ]);

        $invalidTarget = Material::create([
            'code' => 'MAT-TGT-PCS',
            'name' => 'Kemasan Box Pcs',
            'category' => 'Kemasan',
            'unit' => 'pcs',
            'stock_quantity' => 10.00,
        ]);

        // Initiate batch
        $initResponse = $this->actingAs($this->productionUser)->post(route('production.sorts.store'), [
            'source_material_id' => $raw->id,
            'initial_weight' => 20.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Petugas Sortir',
        ]);
        $initResponse->assertRedirect();

        $sortBatch = MaterialSortBatch::where('source_material_id', $raw->id)->first();
        $this->assertNotNull($sortBatch);

        // Submit report with target having 'pcs' unit instead of 'kg'
        $reportResponse = $this->actingAs($this->productionUser)->post(route('production.sorts.store-report', $sortBatch), [
            'dried_weight' => 20.00,
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Mandor Penimbang',
            'items' => [
                ['target_material_id' => $invalidTarget->id, 'result_weight' => 20.00],
            ],
        ]);

        $reportResponse->assertRedirect();
        $reportResponse->assertSessionHasErrors('report');

        // Batch should still be in_progress
        $sortBatch->refresh();
        $this->assertEquals('in_progress', $sortBatch->status);
    }

    public function test_production_batch_starts_from_tembak_and_tracks_pipeline(): void
    {
        $product = Product::create([
            'sku' => 'GHR-KLM-001',
            'name' => 'Kayu Gaharu KLM Super',
            'unit' => 'kg',
            'stock_quantity' => 0,
            'price' => 5000000,
        ]);

        $wood = Material::create([
            'code' => 'MAT-WOOD-01',
            'name' => 'Kayu Tembak SP',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 50,
            'unit_cost' => 200000,
        ]);

        $resin = Material::create([
            'code' => 'MAT-RESIN-01',
            'name' => 'Resin Gaharu',
            'category' => 'Minyak & Resin',
            'unit' => 'kg',
            'stock_quantity' => 20,
            'unit_cost' => 1000000,
        ]);

        // Buat batch baru
        $batchPayload = [
            'batch_number' => 'BATCH-623',
            'product_id' => $product->id,
            'finishing_type' => 'bor_vendor',
            'target_quantity' => 10.00,
            'start_date' => now()->toDateString(),
            'pic_name' => 'Operator Tembak Asep',
            'materials' => [
                ['material_id' => $wood->id, 'quantity_used' => 10.00],
                ['material_id' => $resin->id, 'quantity_used' => 3.00],
            ],
        ];

        $res = $this->actingAs($this->productionUser)->post(route('production.batches.store'), $batchPayload);
        $res->assertRedirect();

        $batch = ProductionBatch::where('batch_number', 'BATCH-623')->firstOrFail();
        $this->assertEquals('Tembak', $batch->stage);
        $this->assertEquals('bor_vendor', $batch->finishing_type);

        // Catat Laporan Tahap Tembak (Hasil basah 12 kg, getah sisa 1.5 kg dikembalikan ke gudang)
        $dailyLogPayload = [
            'log_date' => now()->toDateString(),
            'stage' => 'Tembak',
            'work_status' => 'selesai',
            'pic_name' => 'Asep',
            'notes' => 'Proses injeksi selesai, sisa getah ditampung kembali.',
            'wet_result_weight' => 12.00,
            'residual_resin_weight' => 1.50,
            'residual_resin_material_id' => $resin->id,
        ];

        $logRes = $this->actingAs($this->productionUser)->post(
            route('production.batches.store-log', $batch),
            $dailyLogPayload
        );
        $logRes->assertRedirect();

        $batch->refresh();
        $this->assertEquals(12.00, $batch->wet_result_weight);
        $this->assertEquals(1.50, $batch->residual_resin_weight);
        // Stok resin: 20 awal - 3 pakai + 1.5 sisa kembali = 18.5
        $this->assertEquals(18.50, $resin->fresh()->stock_quantity);

        // Catat Laporan Pengiriman Vendor Bor Pak Kholil (khusus KLM)
        $vendorPayload = [
            'log_date' => now()->addDay()->toDateString(),
            'stage' => 'Finishing Bor (Vendor Luar Pak Kholil)',
            'work_status' => 'tertunda',
            'pic_name' => 'Mandor Bambang',
            'notes' => 'Barang dikirim ke workshop Pak Kholil untuk proses bor.',
            'vendor_name' => 'Pak Kholil',
            'vendor_sent_date' => now()->toDateString(),
        ];

        $vRes = $this->actingAs($this->productionUser)->post(
            route('production.batches.store-log', $batch),
            $vendorPayload
        );
        $vRes->assertRedirect();

        $batch->refresh();
        $this->assertEquals('Finishing Bor (Vendor Luar Pak Kholil)', $batch->stage);
        $this->assertEquals('Pak Kholil', $batch->vendor_name);
    }

    public function test_production_batch_supports_flexible_non_linear_stages(): void
    {
        $product = Product::create([
            'sku' => 'GHR-FLX-001',
            'name' => 'Gaharu Fleksibel Special',
            'unit' => 'kg',
            'stock_quantity' => 0,
            'price' => 3500000,
        ]);

        // 1. Batch can start directly at any stage (e.g. Celup)
        $batchPayload = [
            'batch_number' => 'BATCH-FLEX-01',
            'product_id' => $product->id,
            'target_quantity' => 5.00,
            'initial_stage' => 'Celup',
            'start_date' => now()->toDateString(),
            'pic_name' => 'Mandor Fleksibel',
        ];

        $res = $this->actingAs($this->productionUser)->post(route('production.batches.store'), $batchPayload);
        $res->assertRedirect();

        $batch = ProductionBatch::where('batch_number', 'BATCH-FLEX-01')->firstOrFail();
        $this->assertEquals('Celup', $batch->stage);

        // 2. Operator can record next stage skipping Warna straight to Finishing (Kerok)
        $logRes = $this->actingAs($this->productionUser)->post(route('production.batches.store-log', $batch), [
            'log_date' => now()->toDateString(),
            'stage' => 'Finishing (Kerok)',
            'work_status' => 'selesai',
            'pic_name' => 'Operator Kerok',
            'notes' => 'Langsung dikerok manual tanpa pewarnaan.',
        ]);
        $logRes->assertRedirect();
        $this->assertEquals('Finishing (Kerok)', $batch->fresh()->stage);

        // 3. Mark complete & verify inventory increment
        $completeRes = $this->actingAs($this->productionUser)->post(route('production.batches.store-log', $batch), [
            'log_date' => now()->toDateString(),
            'stage' => 'Selesai',
            'actual_quantity' => 4.80,
            'pic_name' => 'QC Officer',
            'notes' => 'Lolos uji mutu dan siap jual.',
        ]);
        $completeRes->assertRedirect();

        $this->assertEquals('completed', $batch->fresh()->status);
        $this->assertEquals(4.80, $batch->fresh()->actual_quantity);
        $this->assertEquals(4, $product->fresh()->stock_quantity); // cast to int
    }
}
