<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\ProductionTembakBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('public');

        $wood = Material::create([
            'code' => 'MAT-TBK-SP',
            'name' => 'Kayu Tembak Grade SP',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 30.00,
            'unit_cost' => 250000,
        ]);
        $resin = Material::create([
            'code' => 'MAT-RESIN-01',
            'name' => 'Resin Gaharu Super',
            'category' => 'Getah',
            'unit' => 'kg',
            'stock_quantity' => 10.00,
            'unit_cost' => 1000000,
        ]);
        $output = Material::create([
            'code' => 'MAT-RES-TBK-SP',
            'name' => 'Kayu Hasil Tembak SP',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'unit_cost' => 500000,
        ]);
        $signatureData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
        $tembakDate = now()->subDays(2)->toDateString();

        $initResponse = $this->actingAs($this->productionUser)->post(route('production.tembaks.store'), [
            'woods' => [
                ['material_id' => $wood->id, 'weight' => 15.00],
            ],
            'resins' => [
                ['material_id' => $resin->id, 'weight' => 4.00],
            ],
            'tembak_date' => $tembakDate,
            'pic_name' => 'Operator Asep',
            'notes' => 'Inisiasi tembak batch 1 kayu SP tekanan 5 bar.',
        ]);

        $batch = ProductionTembakBatch::latest('id')->firstOrFail();
        $initResponse->assertRedirect(route('production.tembaks.show', $batch));
        $this->assertSame(15.0, (float) $wood->fresh()->stock_quantity);
        $this->assertSame(6.0, (float) $resin->fresh()->stock_quantity);
        $this->assertSame(ProductionTembakBatch::STATUS_IN_PROGRESS, $batch->status);
        $this->assertDatabaseHas('production_tembak_materials', [
            'production_tembak_batch_id' => $batch->id,
            'material_id' => $wood->id,
            'type' => 'wood',
            'weight' => 15.00,
        ]);
        $this->assertDatabaseHas('production_tembak_materials', [
            'production_tembak_batch_id' => $batch->id,
            'material_id' => $resin->id,
            'type' => 'resin',
            'weight' => 4.00,
        ]);
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

        $this->actingAs($this->productionUser)
            ->get(route('production.tembaks.show', $batch))
            ->assertOk()
            ->assertSee('Sedang Ditembak')
            ->assertSee('Laporan Hasil Tembak');

        $reportResponse = $this->actingAs($this->productionUser)->post(route('production.tembaks.store-report', $batch), [
            'wet_result_weight' => 18.00,
            'residual_resin_weight' => 1.00,
            'residual_destination_type' => 'new',
            'new_residual_name' => 'Getah Sisa Tembak SP',
            'new_residual_code' => 'MAT-RESIN-SISA',
            'report_date' => $tembakDate,
            'report_pic_name' => 'Mandor Bambang',
            'report_notes' => 'Hasil tembak siap memasuki proses jemur.',
            'signature_data' => $signatureData,
        ]);

        $reportResponse->assertRedirect(route('production.tembaks.show', $batch));
        $reportResponse->assertSessionHasNoErrors();
        $batch->refresh();
        $residualMaterial = Material::where('code', 'MAT-RESIN-SISA')->firstOrFail();
        $this->assertSame(ProductionTembakBatch::STATUS_DRYING, $batch->status);
        $this->assertSame(18.0, (float) $batch->wet_result_weight);
        $this->assertSame(1.0, (float) $batch->residual_resin_weight);
        $this->assertSame($residualMaterial->id, $batch->residual_resin_material_id);
        $this->assertNull($batch->dried_result_weight);
        $this->assertSame(6.0, (float) $resin->fresh()->stock_quantity);
        $this->assertSame(1.0, (float) $residualMaterial->stock_quantity);
        $this->assertSame(0.0, (float) $output->fresh()->stock_quantity);
        $this->assertNotNull($batch->report_signature_path);
        Storage::disk('public')->assertExists($batch->report_signature_path);
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $residualMaterial->id,
            'type' => 'in',
            'reference_number' => $batch->tembak_code,
            'quantity' => 1.00,
        ]);

        $firstDryingResponse = $this->actingAs($this->productionUser)->post(route('production.tembaks.store-drying-log', $batch), [
            'weighed_date' => now()->subDay()->toDateString(),
            'new_weight' => 17.75,
            'pic_name' => 'Mandor Bambang',
            'notes' => 'Timbang jemur hari pertama.',
            'is_completed' => false,
        ]);

        $firstDryingResponse->assertRedirect(route('production.tembaks.show', $batch));
        $firstDryingResponse->assertSessionHasNoErrors();
        $this->assertSame(ProductionTembakBatch::STATUS_DRYING, $batch->fresh()->status);
        $this->assertSame(0.0, (float) $output->fresh()->stock_quantity);
        $this->assertDatabaseHas('production_tembak_drying_logs', [
            'production_tembak_batch_id' => $batch->id,
            'previous_weight' => 18.00,
            'new_weight' => 17.75,
            'shrinkage_weight' => 0.25,
            'is_final' => false,
        ]);

        $finalDryingResponse = $this->actingAs($this->productionUser)->post(route('production.tembaks.store-drying-log', $batch), [
            'weighed_date' => now()->toDateString(),
            'new_weight' => 17.50,
            'pic_name' => 'Mandor Bambang',
            'notes' => 'Proses jemur selesai.',
            'is_completed' => true,
            'destination_type' => 'existing',
            'output_material_id' => $output->id,
        ]);

        $finalDryingResponse->assertRedirect(route('production.tembaks.show', $batch));
        $finalDryingResponse->assertSessionHasNoErrors();
        $batch->refresh();
        $this->assertSame(ProductionTembakBatch::STATUS_COMPLETED, $batch->status);
        $this->assertSame(17.5, (float) $batch->dried_result_weight);
        $this->assertSame($output->id, $batch->output_material_id);
        $this->assertSame(17.5, (float) $output->fresh()->stock_quantity);
        $this->assertSame(2, $batch->dryingLogs()->count());
        $this->assertDatabaseHas('production_tembak_drying_logs', [
            'production_tembak_batch_id' => $batch->id,
            'previous_weight' => 17.75,
            'new_weight' => 17.50,
            'shrinkage_weight' => 0.25,
            'is_final' => true,
        ]);
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $output->id,
            'type' => 'in',
            'reference_number' => $batch->tembak_code,
            'quantity' => 17.50,
        ]);
    }

    public function test_tembak_detail_renders_signatures_relative_to_the_current_request_host(): void
    {
        config(['filesystems.disks.public.url' => 'http://stale-host.test/storage']);

        $batch = ProductionTembakBatch::create([
            'tembak_code' => 'TBK-202609-9001',
            'status' => ProductionTembakBatch::STATUS_DRYING,
            'tembak_date' => now()->subDays(3)->toDateString(),
            'pic_name' => 'Operator Asep',
            'signature_path' => 'signatures/sig_tbk_init_regression.png',
            'wet_result_weight' => 18.00,
            'residual_resin_weight' => 0.00,
            'report_date' => now()->subDays(2)->toDateString(),
            'report_pic_name' => 'Mandor Bambang',
            'report_signature_path' => 'signatures/sig_tbk_report_regression.png',
        ]);

        $response = $this->actingAs($this->productionUser)->get(route('production.tembaks.show', $batch));

        $response->assertOk();
        $response->assertSee('Tanda Tangan Inisiasi');
        $response->assertSee('src="'.asset('storage/signatures/sig_tbk_init_regression.png').'"', false);
        $response->assertSee('src="'.asset('storage/signatures/sig_tbk_report_regression.png').'"', false);
        $response->assertDontSee('stale-host.test', false);
    }

    public function test_initiate_tembak_fails_if_wood_or_resin_stock_insufficient(): void
    {
        $wood = Material::create([
            'code' => 'MAT-TBK-LOW',
            'name' => 'Kayu Tembak Minim',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 5.00,
        ]);
        $resin = Material::create([
            'code' => 'MAT-RSN-LOW',
            'name' => 'Resin Minim',
            'category' => 'Getah',
            'unit' => 'kg',
            'stock_quantity' => 2.00,
        ]);

        $woodResponse = $this->actingAs($this->productionUser)->post(route('production.tembaks.store'), [
            'woods' => [
                ['material_id' => $wood->id, 'weight' => 10.00],
            ],
            'resins' => [
                ['material_id' => $resin->id, 'weight' => 1.00],
            ],
            'tembak_date' => now()->toDateString(),
            'pic_name' => 'Asep',
        ]);

        $woodResponse->assertRedirect();
        $woodResponse->assertSessionHasErrors('tembak');
        $this->assertSame(5.0, (float) $wood->fresh()->stock_quantity);

        $resinResponse = $this->actingAs($this->productionUser)->post(route('production.tembaks.store'), [
            'woods' => [
                ['material_id' => $wood->id, 'weight' => 3.00],
            ],
            'resins' => [
                ['material_id' => $resin->id, 'weight' => 5.00],
            ],
            'tembak_date' => now()->toDateString(),
            'pic_name' => 'Asep',
        ]);

        $resinResponse->assertRedirect();
        $resinResponse->assertSessionHasErrors('tembak');
        $this->assertSame(2.0, (float) $resin->fresh()->stock_quantity);
    }
}
