<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\MaterialBranch;
use App\Models\Product;
use App\Models\ProductionFinishingSession;
use App\Models\ProductionTembakBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionFinishingTest extends TestCase
{
    use RefreshDatabase;

    private const SIGNATURE_DATA = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    protected User $productionUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productionUser = User::factory()->create([
            'role' => UserRole::PRODUCTION,
        ]);
    }

    public function test_production_user_can_view_finishing_index_and_create_pages(): void
    {
        Material::create([
            'code' => 'MAT-FIN-SRC',
            'name' => 'Kayu Hasil Tembak Kering',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 20.00,
            'unit_cost' => 500000,
        ]);

        $indexResponse = $this->actingAs($this->productionUser)->get(route('production.finishings.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Finishing');

        $createResponse = $this->actingAs($this->productionUser)->get(route('production.finishings.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Mulai Finishing');
        $createResponse->assertSee('Kayu Hasil Tembak Kering');
    }

    public function test_can_process_complete_finishing_workflow_with_arbitrary_step_order(): void
    {
        Storage::fake('public');

        $source = Material::create([
            'code' => 'MAT-FIN-01',
            'name' => 'Kayu Hasil Tembak Grade A',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 30.00,
            'unit_cost' => 400000,
        ]);
        $methanol = Material::create([
            'code' => 'MAT-METANOL',
            'name' => 'Metanol Teknis',
            'category' => 'Metanol',
            'unit' => 'liter',
            'stock_quantity' => 50.00,
            'unit_cost' => 25000,
        ]);
        $wasteTarget = Material::create([
            'code' => 'MAT-AMPAS-01',
            'name' => 'Ampas Gaharu Terkumpul',
            'category' => 'Ampas Finishing',
            'unit' => 'kg',
            'stock_quantity' => 2.00,
            'unit_cost' => 100000,
        ]);

        // Bahan asal membawa identitas branch dari proses tembak sebelumnya.
        MaterialBranch::create([
            'material_id' => $source->id,
            'branch_code' => 'BR-202609-0001',
            'quantity' => 30.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
            'source_reference' => 'TBK-202609-0001',
        ]);

        $finishingDate = now()->subDays(4)->toDateString();

        $initResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store'), [
            'source_material_id' => $source->id,
            'initial_weight' => 20.00,
            'finishing_date' => $finishingDate,
            'pic_name' => 'Operator Dedi',
            'notes' => 'Finishing pesanan khusus.',
            'signature_data' => self::SIGNATURE_DATA,
        ]);

        $session = ProductionFinishingSession::latest('id')->firstOrFail();
        $initResponse->assertRedirect(route('production.finishings.show', $session));
        $initResponse->assertSessionHasNoErrors();

        $this->assertSame(10.0, (float) $source->fresh()->stock_quantity);
        $this->assertSame(ProductionFinishingSession::STATUS_IN_PROGRESS, $session->status);
        $this->assertSame(20.0, (float) $session->initial_weight);
        $this->assertSame(20.0, (float) $session->current_weight);
        // Branch bahan asal terbawa apa adanya karena hanya satu branch.
        $this->assertSame('BR-202609-0001', $session->branch_code);
        $this->assertFalse($session->branch_is_merged);
        $this->assertNotNull($session->signature_path);
        Storage::disk('public')->assertExists($session->signature_path);
        $this->assertDatabaseHas('material_branches', [
            'material_id' => $source->id,
            'branch_code' => 'BR-202609-0001',
            'quantity' => 10.00,
        ]);
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $source->id,
            'type' => 'out',
            'reference_number' => $session->finishing_code,
            'quantity' => 20.00,
        ]);

        // Proses pertama: celup metanol. Bahan pendukung tercatat beserta harganya.
        $celupResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store-step', $session), [
            'process_type' => 'celup',
            'step_date' => now()->subDays(3)->toDateString(),
            'weight_after' => 19.50,
            'waste_weight' => 0,
            'waste_destination_type' => 'none',
            'pic_name' => 'Operator Dedi',
            'notes' => 'Celup metanol 30 menit.',
            'materials' => [
                ['material_id' => $methanol->id, 'quantity' => 4.00],
            ],
        ]);

        $celupResponse->assertRedirect(route('production.finishings.show', $session));
        $celupResponse->assertSessionHasNoErrors();
        $this->assertSame(46.0, (float) $methanol->fresh()->stock_quantity);
        $this->assertSame(19.5, (float) $session->fresh()->current_weight);
        $this->assertDatabaseHas('production_finishing_steps', [
            'production_finishing_session_id' => $session->id,
            'sequence' => 1,
            'process_type' => 'celup',
            'weight_before' => 20.00,
            'weight_after' => 19.50,
            'material_cost' => 100000.00,
        ]);
        $this->assertDatabaseHas('production_finishing_step_materials', [
            'material_id' => $methanol->id,
            'quantity' => 4.00,
            'unit_cost' => 25000.00,
        ]);
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $methanol->id,
            'type' => 'out',
            'reference_number' => $session->finishing_code,
            'quantity' => 4.00,
        ]);

        // Proses kedua langsung ke bor, melewati molen, membuktikan urutan bebas.
        $borResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store-step', $session), [
            'process_type' => 'bor',
            'step_date' => now()->subDays(2)->toDateString(),
            'weight_after' => 18.00,
            'waste_weight' => 1.00,
            'waste_destination_type' => 'existing',
            'waste_material_id' => $wasteTarget->id,
            'pic_name' => 'Operator Rina',
            'notes' => 'Bor lubang gantungan.',
        ]);

        $borResponse->assertRedirect(route('production.finishings.show', $session));
        $borResponse->assertSessionHasNoErrors();
        $this->assertSame(18.0, (float) $session->fresh()->current_weight);
        $this->assertSame(3.0, (float) $wasteTarget->fresh()->stock_quantity);
        $this->assertDatabaseHas('production_finishing_steps', [
            'production_finishing_session_id' => $session->id,
            'sequence' => 2,
            'process_type' => 'bor',
            'waste_weight' => 1.00,
            'waste_material_id' => $wasteTarget->id,
        ]);
        // Ampas tetap membawa identitas branch produksinya.
        $this->assertDatabaseHas('material_branches', [
            'material_id' => $wasteTarget->id,
            'branch_code' => 'BR-202609-0001',
            'quantity' => 1.00,
            'source_type' => MaterialBranch::SOURCE_FINISHING,
        ]);
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $wasteTarget->id,
            'type' => 'in',
            'reference_number' => $session->finishing_code,
            'quantity' => 1.00,
        ]);

        // Proses ketiga: molen dengan ampas disimpan sebagai barang gudang baru.
        $molenResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store-step', $session), [
            'process_type' => 'molen',
            'step_date' => now()->subDay()->toDateString(),
            'weight_after' => 17.00,
            'waste_weight' => 0.60,
            'waste_destination_type' => 'new',
            'new_waste_name' => 'Ampas Molen Grade A',
            'new_waste_code' => 'MAT-AMPAS-MOLEN',
            'pic_name' => 'Operator Rina',
        ]);

        $molenResponse->assertRedirect(route('production.finishings.show', $session));
        $molenResponse->assertSessionHasNoErrors();
        $newWaste = Material::where('code', 'MAT-AMPAS-MOLEN')->firstOrFail();
        $this->assertSame('Ampas Finishing', $newWaste->category);
        $this->assertSame(0.6, (float) $newWaste->stock_quantity);
        $this->assertSame(17.0, (float) $session->fresh()->current_weight);
        $this->assertSame(3, $session->fresh()->steps()->count());

        // Sesi belum selesai sebelum admin menyatakannya.
        $this->assertSame(ProductionFinishingSession::STATUS_IN_PROGRESS, $session->fresh()->status);

        $activeIndexResponse = $this->actingAs($this->productionUser)->get(route('production.finishings.index'));
        $activeIndexResponse->assertOk();
        $activeIndexResponse->assertSee($session->finishing_code);
        $activeIndexResponse->assertSee('BR-202609-0001');
        $activeIndexResponse->assertSee('Sedang Finishing');
        $activeIndexResponse->assertSee('Terakhir Molen (Digiling Halus)');

        $inProgressResponse = $this->actingAs($this->productionUser)->get(route('production.finishings.show', $session));
        $inProgressResponse->assertOk();
        $inProgressResponse->assertSee('Catat Proses Finishing');
        $inProgressResponse->assertSee('Nyatakan Finishing Selesai');
        $inProgressResponse->assertSee('Riwayat Proses & Perpindahan', false);
        $inProgressResponse->assertSee('BR-202609-0001');

        $completeResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.complete', $session), [
            'completed_date' => now()->toDateString(),
            'completion_pic_name' => 'Mandor Bambang',
            'output_quantity' => 17,
            'completion_notes' => 'Lolos quality control.',
            'product_destination_type' => 'new',
            'new_product_name' => 'Gaharu Finishing Grade A',
            'new_product_sku' => 'PRD-GHR-A',
            'new_product_price' => 1500000,
            'signature_data' => self::SIGNATURE_DATA,
        ]);

        $completeResponse->assertRedirect(route('production.finishings.show', $session));
        $completeResponse->assertSessionHasNoErrors();

        $session->refresh();
        $product = Product::where('sku', 'PRD-GHR-A')->firstOrFail();
        $this->assertSame(ProductionFinishingSession::STATUS_COMPLETED, $session->status);
        $this->assertSame(17.0, (float) $session->output_weight);
        $this->assertSame(17, $session->output_quantity);
        $this->assertSame($product->id, $session->product_id);
        $this->assertSame(17, (int) $product->stock_quantity);
        $this->assertNotNull($session->completion_signature_path);
        Storage::disk('public')->assertExists($session->completion_signature_path);

        // Identitas branch terbawa sampai ke barang jadi siap jual.
        $this->assertDatabaseHas('product_branches', [
            'id' => $session->product_branch_id,
            'product_id' => $product->id,
            'branch_code' => 'BR-202609-0001',
            'quantity' => 17,
        ]);

        $showResponse = $this->actingAs($this->productionUser)->get(route('production.finishings.show', $session));
        $showResponse->assertOk();
        $showResponse->assertSee('BR-202609-0001');
        $showResponse->assertSee('Gaharu Finishing Grade A');
        $showResponse->assertSee('Celup Metanol');
        $showResponse->assertSee('Bor (Dibentuk)');
        $showResponse->assertSee('Molen (Digiling Halus)');
    }

    public function test_finishing_cannot_be_recorded_or_completed_after_it_is_finished(): void
    {
        $source = Material::create([
            'code' => 'MAT-FIN-02',
            'name' => 'Kayu Tembak Selesai',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 12.00,
            'unit_cost' => 300000,
        ]);
        $product = Product::create([
            'sku' => 'PRD-EXIST',
            'name' => 'Gaharu Siap Jual',
            'price' => 900000,
            'stock_quantity' => 5,
            'unit' => 'kg',
        ]);

        $this->actingAs($this->productionUser)->post(route('production.finishings.store'), [
            'source_material_id' => $source->id,
            'initial_weight' => 10.00,
            'finishing_date' => now()->subDays(2)->toDateString(),
            'pic_name' => 'Operator Dedi',
        ]);

        $session = ProductionFinishingSession::latest('id')->firstOrFail();

        $this->actingAs($this->productionUser)->post(route('production.finishings.store-step', $session), [
            'process_type' => 'kerok',
            'step_date' => now()->subDay()->toDateString(),
            'weight_after' => 9.00,
            'waste_weight' => 0,
            'waste_destination_type' => 'none',
            'pic_name' => 'Operator Dedi',
        ]);

        $this->actingAs($this->productionUser)->post(route('production.finishings.complete', $session), [
            'completed_date' => now()->toDateString(),
            'completion_pic_name' => 'Mandor Bambang',
            'output_quantity' => 9,
            'product_destination_type' => 'existing',
            'product_id' => $product->id,
            'signature_data' => self::SIGNATURE_DATA,
        ]);

        $this->assertSame(ProductionFinishingSession::STATUS_COMPLETED, $session->fresh()->status);
        $this->assertSame(14, (int) $product->fresh()->stock_quantity);

        $lateStepResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store-step', $session), [
            'process_type' => 'molen',
            'step_date' => now()->toDateString(),
            'weight_after' => 8.00,
            'waste_weight' => 0,
            'waste_destination_type' => 'none',
            'pic_name' => 'Operator Dedi',
        ]);

        $lateStepResponse->assertSessionHasErrors('step');
        $this->assertSame(1, $session->fresh()->steps()->count());

        $doubleCompleteResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.complete', $session), [
            'completed_date' => now()->toDateString(),
            'completion_pic_name' => 'Mandor Bambang',
            'output_quantity' => 5,
            'product_destination_type' => 'existing',
            'product_id' => $product->id,
            'signature_data' => self::SIGNATURE_DATA,
        ]);

        $doubleCompleteResponse->assertSessionHasErrors('completion');
        $this->assertSame(14, (int) $product->fresh()->stock_quantity);
    }

    public function test_finishing_rejects_waste_above_process_shrinkage_and_insufficient_stock(): void
    {
        $source = Material::create([
            'code' => 'MAT-FIN-03',
            'name' => 'Kayu Tembak Uji',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 8.00,
            'unit_cost' => 250000,
        ]);

        $overStockResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store'), [
            'source_material_id' => $source->id,
            'initial_weight' => 20.00,
            'finishing_date' => now()->toDateString(),
            'pic_name' => 'Operator Dedi',
        ]);

        $overStockResponse->assertSessionHasErrors('finishing');
        $this->assertSame(8.0, (float) $source->fresh()->stock_quantity);
        $this->assertSame(0, ProductionFinishingSession::count());

        $this->actingAs($this->productionUser)->post(route('production.finishings.store'), [
            'source_material_id' => $source->id,
            'initial_weight' => 8.00,
            'finishing_date' => now()->subDay()->toDateString(),
            'pic_name' => 'Operator Dedi',
        ]);

        $session = ProductionFinishingSession::latest('id')->firstOrFail();

        // Susut proses hanya 0,50 kg sehingga ampas 2 kg harus ditolak.
        $overWasteResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store-step', $session), [
            'process_type' => 'molen',
            'step_date' => now()->toDateString(),
            'weight_after' => 7.50,
            'waste_weight' => 2.00,
            'waste_destination_type' => 'none',
            'pic_name' => 'Operator Dedi',
        ]);

        $overWasteResponse->assertSessionHasErrors('step');
        $this->assertSame(0, $session->fresh()->steps()->count());
        $this->assertSame(8.0, (float) $session->fresh()->current_weight);

        // Berat setelah proses tidak boleh melebihi berat sebelumnya.
        $heavierResponse = $this->actingAs($this->productionUser)->post(route('production.finishings.store-step', $session), [
            'process_type' => 'kerok',
            'step_date' => now()->toDateString(),
            'weight_after' => 9.00,
            'waste_weight' => 0,
            'waste_destination_type' => 'none',
            'pic_name' => 'Operator Dedi',
        ]);

        $heavierResponse->assertSessionHasErrors('step');
        $this->assertSame(0, $session->fresh()->steps()->count());
    }

    public function test_finishing_marks_merged_branch_when_source_stock_mixes_two_branches(): void
    {
        $source = Material::create([
            'code' => 'MAT-FIN-MIX',
            'name' => 'Kayu Tembak Gabungan',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 20.00,
            'unit_cost' => 350000,
        ]);

        MaterialBranch::create([
            'material_id' => $source->id,
            'branch_code' => 'BR-202609-0001',
            'quantity' => 12.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
        ]);
        MaterialBranch::create([
            'material_id' => $source->id,
            'branch_code' => 'BR-202609-0002',
            'quantity' => 8.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
        ]);

        $this->actingAs($this->productionUser)->post(route('production.finishings.store'), [
            'source_material_id' => $source->id,
            'initial_weight' => 10.00,
            'finishing_date' => now()->toDateString(),
            'pic_name' => 'Operator Dedi',
        ]);

        $session = ProductionFinishingSession::latest('id')->firstOrFail();

        $this->assertTrue($session->branch_is_merged);
        $this->assertStringStartsWith('BRM-', (string) $session->branch_code);
        $this->assertSame(['BR-202609-0001', 'BR-202609-0002'], $session->source_branch_codes);
        $this->assertStringContainsString('(Gabungan)', $session->branch_label);

        // Pemakaian 10 kg dibagi proporsional: 6 kg dari branch 0001, 4 kg dari 0002.
        $this->assertDatabaseHas('material_branches', [
            'material_id' => $source->id,
            'branch_code' => 'BR-202609-0001',
            'quantity' => 6.00,
        ]);
        $this->assertDatabaseHas('material_branches', [
            'material_id' => $source->id,
            'branch_code' => 'BR-202609-0002',
            'quantity' => 4.00,
        ]);
    }

    public function test_tembak_batch_issues_a_branch_identity_that_reaches_the_warehouse(): void
    {
        $wood = Material::create([
            'code' => 'MAT-TBK-BR',
            'name' => 'Kayu Tembak Branch',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 25.00,
            'unit_cost' => 200000,
        ]);
        $resin = Material::create([
            'code' => 'MAT-RSN-BR',
            'name' => 'Resin Branch',
            'category' => 'Getah',
            'unit' => 'kg',
            'stock_quantity' => 8.00,
            'unit_cost' => 900000,
        ]);
        $output = Material::create([
            'code' => 'MAT-OUT-BR',
            'name' => 'Hasil Tembak Branch',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'unit_cost' => 0,
        ]);
        $tembakDate = now()->subDays(3)->toDateString();

        $this->actingAs($this->productionUser)->post(route('production.tembaks.store'), [
            'woods' => [['material_id' => $wood->id, 'weight' => 10.00]],
            'resins' => [['material_id' => $resin->id, 'weight' => 3.00]],
            'tembak_date' => $tembakDate,
            'pic_name' => 'Operator Asep',
        ]);

        $batch = ProductionTembakBatch::latest('id')->firstOrFail();
        $this->assertNotNull($batch->branch_code);
        $this->assertStringStartsWith('BR-', $batch->branch_code);

        $this->actingAs($this->productionUser)->post(route('production.tembaks.store-report', $batch), [
            'wet_result_weight' => 12.00,
            'residual_resin_weight' => 0,
            'residual_destination_type' => 'none',
            'report_date' => $tembakDate,
            'report_pic_name' => 'Mandor Bambang',
            'signature_data' => self::SIGNATURE_DATA,
        ]);

        $this->actingAs($this->productionUser)->post(route('production.tembaks.store-drying-log', $batch), [
            'weighed_date' => now()->toDateString(),
            'new_weight' => 11.00,
            'pic_name' => 'Mandor Bambang',
            'is_completed' => true,
            'destination_type' => 'existing',
            'output_material_id' => $output->id,
        ]);

        // Hasil jemur masuk gudang sambil membawa identitas branch tembaknya.
        $this->assertDatabaseHas('material_branches', [
            'material_id' => $output->id,
            'branch_code' => $batch->branch_code,
            'quantity' => 11.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
        ]);
        $this->assertSame($batch->branch_code, $output->fresh()->branch_label);
    }
}
