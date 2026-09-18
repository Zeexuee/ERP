<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\ProductionTembakBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionTembakTest extends TestCase
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

    public function test_production_user_can_view_tembak_index_and_create_pages(): void
    {
        $response = $this->actingAs($this->productionUser)->get(route('production.tembaks.index'));
        $response->assertOk();
        $response->assertSee('Tembak Kayu');

        $createResponse = $this->actingAs($this->productionUser)->get(route('production.tembaks.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Inisiasi Tembak Kayu');
    }

    public function test_can_process_complete_tembak_workflow(): void
    {
        // 1. Setup Bahan: Kayu Tembak SP 30 kg & Resin Gaharu 10 kg
        $wood = Material::create([
            'code' => 'MAT-TBK-SP',
            'name' => 'Kayu Tembak Grade SP',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 30.00,
            'unit_cost' => 250000,
        ]);

        $resin = Material::create([
            'code' => 'MAT-RESIN-01',
            'name' => 'Resin Gaharu Super',
            'category' => 'Minyak & Resin',
            'unit' => 'kg',
            'stock_quantity' => 10.00,
            'unit_cost' => 1000000,
        ]);

        $output = Material::create([
            'code' => 'MAT-RES-TBK-SP',
            'name' => 'Kayu Hasil Tembak SP',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'unit_cost' => 500000,
        ]);

        // 2. Inisiasi Tembak: Ambil 15 kg Kayu + 4 kg Resin
        $initPayload = [
            'wood_material_id' => $wood->id,
            'wood_weight' => 15.00,
            'resin_material_id' => $resin->id,
            'resin_weight' => 4.00,
            'tembak_date' => now()->toDateString(),
            'pic_name' => 'Operator Asep',
            'notes' => 'Inisiasi tembak batch 1 kayu SP tekanan 5 bar.',
        ];

        $initRes = $this->actingAs($this->productionUser)->post(route('production.tembaks.store'), $initPayload);
        $initRes->assertRedirect();

        // Verifikasi stok terpotong: Kayu 30 - 15 = 15, Resin 10 - 4 = 6
        $this->assertEquals(15.00, (float) $wood->fresh()->stock_quantity);
        $this->assertEquals(6.00, (float) $resin->fresh()->stock_quantity);

        $batch = ProductionTembakBatch::latest('id')->first();
        $this->assertNotNull($batch);
        $this->assertEquals('in_progress', $batch->status);
        $this->assertEquals(15.00, (float) $batch->wood_weight);
        $this->assertEquals(4.00, (float) $batch->resin_weight);

        // Verifikasi mutasi log keluar
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $wood->id,
            'type' => 'out',
            'reference_number' => $batch->tembak_code,
            'quantity' => 15.00,
        ]);
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $resin->id,
            'type' => 'out',
            'reference_number' => $batch->tembak_code,
            'quantity' => 4.00,
        ]);

        // 3. Lihat Halaman Detail
        $showRes = $this->actingAs($this->productionUser)->get(route('production.tembaks.show', $batch));
        $showRes->assertOk();
        $showRes->assertSee('Sedang Ditembak');
        $showRes->assertSee('Laporan Hasil Tembak');

        // 4. Submit Laporan: Hasil Basah 18 kg, Getah Sisa 1 kg (kembali ke gudang), Hasil Kering 17.5 kg
        $reportPayload = [
            'wet_result_weight' => 18.00,
            'residual_resin_weight' => 1.00,
            'residual_resin_material_id' => $resin->id,
            'dried_result_weight' => 17.50,
            'output_material_id' => $output->id,
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Mandor Bambang',
            'report_notes' => 'Proses penjemuran 2 hari selesai sempurna.',
        ];

        $reportRes = $this->actingAs($this->productionUser)->post(route('production.tembaks.store-report', $batch), $reportPayload);
        $reportRes->assertRedirect();

        // 5. Verifikasi Stok Akhir
        // Sisa resin 1 kg kembali ke gudang: 6 + 1 = 7 kg
        $this->assertEquals(7.00, (float) $resin->fresh()->stock_quantity);
        // Hasil tembak kering 17.5 kg masuk ke bahan output: 0 + 17.5 = 17.5 kg
        $this->assertEquals(17.50, (float) $output->fresh()->stock_quantity);

        // Verifikasi status batch selesai
        $batch->refresh();
        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(18.00, (float) $batch->wet_result_weight);
        $this->assertEquals(1.00, (float) $batch->residual_resin_weight);
        $this->assertEquals(17.50, (float) $batch->dried_result_weight);
        $this->assertEquals($output->id, $batch->output_material_id);

        // Verifikasi mutasi log masuk
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $resin->id,
            'type' => 'in',
            'reference_number' => $batch->tembak_code,
            'quantity' => 1.00,
        ]);
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $output->id,
            'type' => 'in',
            'reference_number' => $batch->tembak_code,
            'quantity' => 17.50,
        ]);
    }

    public function test_initiate_tembak_fails_if_wood_or_resin_stock_insufficient(): void
    {
        $wood = Material::create([
            'code' => 'MAT-TBK-LOW',
            'name' => 'Kayu Tembak Minim',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 5.00,
        ]);

        $resin = Material::create([
            'code' => 'MAT-RSN-LOW',
            'name' => 'Resin Minim',
            'category' => 'Minyak & Resin',
            'unit' => 'kg',
            'stock_quantity' => 2.00,
        ]);

        // Wood exceeds stock
        $res = $this->actingAs($this->productionUser)->post(route('production.tembaks.store'), [
            'wood_material_id' => $wood->id,
            'wood_weight' => 10.00, // Stock only 5
            'resin_material_id' => $resin->id,
            'resin_weight' => 1.00,
            'tembak_date' => now()->toDateString(),
            'pic_name' => 'Asep',
        ]);

        $res->assertRedirect();
        $res->assertSessionHasErrors('tembak');
        $this->assertEquals(5.00, (float) $wood->fresh()->stock_quantity);

        // Resin exceeds stock
        $res2 = $this->actingAs($this->productionUser)->post(route('production.tembaks.store'), [
            'wood_material_id' => $wood->id,
            'wood_weight' => 3.00,
            'resin_material_id' => $resin->id,
            'resin_weight' => 5.00, // Stock only 2
            'tembak_date' => now()->toDateString(),
            'pic_name' => 'Asep',
        ]);

        $res2->assertRedirect();
        $res2->assertSessionHasErrors('tembak');
        $this->assertEquals(2.00, (float) $resin->fresh()->stock_quantity);
    }
}
