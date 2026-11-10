<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\User;
use App\Services\Production\MaterialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_user_can_view_materials_warehouse_index(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'MAT-TEST-001',
            'name' => 'Kayu Medang Bahan Dasar',
            'category' => 'Kayu Dasar',
            'unit' => 'kg',
            'stock_quantity' => 100.00,
            'minimum_stock' => 20.00,
            'unit_cost' => 250000.00,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('production.materials.index'));
        $response->assertStatus(200);
        $response->assertSee('Warehouse');
        $response->assertSee('Barang Gudang');
        $response->assertSee('Sortir Kayu');
        $response->assertSee('Barang Masuk');
        $response->assertSee('MAT-TEST-001');
        $response->assertSee('Kayu Medang Bahan Dasar');
        $response->assertSee('100.0');
        $response->assertSee('kg');
        $response->assertSee('Asal / Pemasok');
        $response->assertSee('Riwayat Timbang');
        $response->assertSee('Riwayat Proses');
        $response->assertSee('0 Riwayat');
        $response->assertSee('0 Proses');
        $response->assertSee('recountDetail'.$material->id);
        $response->assertSee('processDetail'.$material->id);
        $response->assertDontSee('Biaya / Unit');
        $response->assertDontSee('Rincian Branch');
        $response->assertDontSee('Tanpa branch');
    }

    public function test_production_user_can_record_stock_in_receipt_and_increments_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'MAT-TEST-002',
            'name' => 'Minyak Gaharu Kalimantan',
            'category' => 'Minyak & Resin',
            'unit' => 'tola',
            'stock_quantity' => 10.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 1200000.00,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.materials.store-receipt'), [
            'material_id' => $material->id,
            'material_name' => 'Minyak Gaharu Kalimantan',
            'unit' => 'tola',
            'quantity' => 15.00,
            'unit_cost' => 1250000.00,
            'source_or_supplier' => 'Distilasi Merauke',
            'received_date' => '2026-09-04',
            'received_by' => 'Mandor Gudang',
            'notes' => 'Aroma pekat kualitas super.',
        ]);

        $response->assertRedirect(route('production.materials.index'));
        $response->assertSessionHas('success');

        // Verify receipt created
        $this->assertDatabaseHas('material_receipts', [
            'material_id' => $material->id,
            'quantity' => 15.00,
            'source_or_supplier' => 'Distilasi Merauke',
        ]);

        // Verify material log created for stock in
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $material->id,
            'type' => 'in',
            'quantity' => 15.00,
            'actor_by' => 'Mandor Gudang',
        ]);

        // Verify stock incremented: 10 + 15 = 25
        $material->refresh();
        $this->assertEquals(25.00, (float) $material->stock_quantity);
        $this->assertEquals(1250000.00, (float) $material->unit_cost);
    }

    public function test_production_user_can_create_new_material_automatically_on_receipt(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $this->actingAs($user);

        $response = $this->post(route('production.materials.store-receipt'), [
            'material_name' => 'Gaharu Super Merauke Baru',
            'category' => 'Kayu Olahan Super',
            'unit' => 'kg',
            'ordered_quantity' => 60.00,
            'quantity' => 50.00,
            'unit_cost' => 500000.00,
            'source_or_supplier' => 'Mitra Hutan Papua',
            'received_date' => '2026-09-05',
            'received_by' => 'Petugas Gudang Utama',
            'notes' => 'Bahan baku baru otomatis terdaftar.',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertRedirect(route('production.materials.index'));

        // Verify material auto-created
        $newMaterial = Material::where('name', 'Gaharu Super Merauke Baru')->first();
        $this->assertNotNull($newMaterial);
        $this->assertEquals('Kayu Olahan Super', $newMaterial->category);
        $this->assertEquals('kg', $newMaterial->unit);
        $this->assertEquals(50.00, (float) $newMaterial->stock_quantity);

        // Verify receipt recorded with ordered_quantity and actual received quantity
        $this->assertDatabaseHas('material_receipts', [
            'material_id' => $newMaterial->id,
            'ordered_quantity' => 60.00,
            'quantity' => 50.00,
            'source_or_supplier' => 'Mitra Hutan Papua',
        ]);
    }

    public function test_production_user_can_update_material_details(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'MAT-EDIT-001',
            'name' => 'Kayu Gaharu Lama',
            'category' => 'Kayu Mentah',
            'unit' => 'kg',
            'stock_quantity' => 20.00,
            'minimum_stock' => 5.00,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('production.materials.update', $material), [
            'code' => 'MAT-EDIT-NEW01',
            'name' => 'Kayu Gaharu Grade A Perubahan',
            'category' => 'Kayu Super Premium',
            'minimum_stock' => 15.00,
        ]);

        $response->assertRedirect();
        $material->refresh();

        $this->assertEquals('MAT-EDIT-NEW01', $material->code);
        $this->assertEquals('Kayu Gaharu Grade A Perubahan', $material->name);
        $this->assertEquals('Kayu Super Premium', $material->category);
        $this->assertEquals(15.00, (float) $material->minimum_stock);
    }

    public function test_production_user_can_recount_material_stock_and_updates_last_weighed_at(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'MAT-RECOUNT-001',
            'name' => 'Serbuk Gaharu Super',
            'category' => 'Serbuk & Bio',
            'unit' => 'kg',
            'stock_quantity' => 100.00,
            'minimum_stock' => 10.00,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.materials.recount', $material), [
            'actual_stock' => 85.50,
            'weighed_by' => 'Ahmad Petugas Gudang',
            'notes' => 'Stock opname fisik bulanan.',
        ]);

        $response->assertRedirect();
        $material->refresh();

        $this->assertEquals(85.50, (float) $material->stock_quantity);
        $this->assertNotNull($material->last_weighed_at);
        $this->assertEquals('Ahmad Petugas Gudang', $material->last_weighed_by);

        // Verify material log created for recount
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $material->id,
            'type' => 'recount',
            'quantity' => 85.50,
            'actor_by' => 'Ahmad Petugas Gudang (Warehouse)',
        ]);
    }

    public function test_sales_user_cannot_access_production_warehouse_directly(): void
    {
        $salesUser = User::factory()->create(['role' => UserRole::SALES]);
        $this->actingAs($salesUser);

        $response = $this->get(route('production.materials.index'));
        $response->assertStatus(403);
    }

    public function test_production_user_can_sort_material_into_new_material(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $sourceMaterial = Material::create([
            'code' => 'MAT-SORT-001',
            'name' => 'Kayu Gaharu Mentah Utama',
            'category' => 'Kayu Mentah',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 10.00,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.materials.sort', $sourceMaterial), [
            'sorted_quantity' => 15.00,
            'destination_type' => 'new',
            'new_name' => 'Serbuk Hasil Sortir Merauke',
            'new_category' => 'Serbuk & Bio',
            'actor_by' => 'Petugas Sortir Tim A',
            'notes' => 'Pemisahan serbuk halus dari kayu mentah.',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sourceMaterial->refresh();
        $this->assertEquals(35.00, (float) $sourceMaterial->stock_quantity);

        $newMaterial = Material::where('name', 'Serbuk Hasil Sortir Merauke')->first();
        $this->assertNotNull($newMaterial);
        $this->assertEquals('kg', $newMaterial->unit);
        $this->assertEquals(15.00, (float) $newMaterial->stock_quantity);

        $outLog = MaterialLog::where('material_id', $sourceMaterial->id)->where('type', 'out')->first();
        $this->assertNotNull($outLog);
        $this->assertNotNull($outLog->signature_path);

        $inLog = MaterialLog::where('material_id', $newMaterial->id)->where('type', 'in')->first();
        $this->assertNotNull($inLog);
        $this->assertNotNull($inLog->signature_path);
    }

    public function test_production_user_can_sort_material_and_merge_into_existing_same_unit_material(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $sourceMaterial = Material::create([
            'code' => 'MAT-SORT-002',
            'name' => 'Serbuk Kasar A',
            'category' => 'Serbuk',
            'unit' => 'kg',
            'stock_quantity' => 40.00,
        ]);

        $targetMaterial = Material::create([
            'code' => 'MAT-SORT-003',
            'name' => 'Serbuk Campuran Gudang',
            'category' => 'Serbuk',
            'unit' => 'kg',
            'stock_quantity' => 20.00,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.materials.sort', $sourceMaterial), [
            'sorted_quantity' => 10.00,
            'destination_type' => 'existing',
            'existing_material_id' => $targetMaterial->id,
            'actor_by' => 'Petugas Sortir Tim B',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sourceMaterial->refresh();
        $targetMaterial->refresh();

        $this->assertEquals(30.00, (float) $sourceMaterial->stock_quantity);
        $this->assertEquals(30.00, (float) $targetMaterial->stock_quantity);
    }

    public function test_production_user_cannot_merge_material_with_different_unit(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $sourceMaterial = Material::create([
            'code' => 'MAT-SORT-004',
            'name' => 'Kayu Merauke',
            'category' => 'Kayu Mentah',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
        ]);

        $targetMaterial = Material::create([
            'code' => 'MAT-SORT-005',
            'name' => 'Kemasan Kotak Kayu',
            'category' => 'Kemasan',
            'unit' => 'pcs',
            'stock_quantity' => 100.00,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.materials.sort', $sourceMaterial), [
            'sorted_quantity' => 5.00,
            'destination_type' => 'existing',
            'existing_material_id' => $targetMaterial->id,
            'actor_by' => 'Petugas Sortir',
        ]);

        $response->assertSessionHasErrors(['sorted_quantity']);

        $sourceMaterial->refresh();
        $targetMaterial->refresh();

        $this->assertEquals(50.00, (float) $sourceMaterial->stock_quantity);
        $this->assertEquals(100.00, (float) $targetMaterial->stock_quantity);
    }

    public function test_production_user_can_export_material_logs_to_excel(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $this->actingAs($user);

        $response = $this->get(route('production.materials.export-logs'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_material_logs_are_pruned_to_maximum_60_records(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'MAT-PRUNE-001',
            'name' => 'Bahan Baku Uji Prune',
            'category' => 'Uji',
            'unit' => 'kg',
            'stock_quantity' => 1000.00,
        ]);

        // Create 65 logs
        for ($i = 1; $i <= 65; $i++) {
            MaterialLog::create([
                'material_id' => $material->id,
                'type' => 'in',
                'reference_number' => "LOG-{$i}",
                'quantity' => 1.00,
                'unit' => 'kg',
                'actor_by' => 'Petugas',
            ]);
        }

        // Run pruning
        MaterialService::pruneOldLogs(60);

        $this->assertEquals(60, MaterialLog::count());
    }

    public function test_production_user_can_register_material_on_the_fly_via_ajax(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $this->actingAs($user);

        $response = $this->postJson(route('production.materials.store'), [
            'name' => 'Kayu Tembak Grade Istimewa',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'unit_cost' => 150000,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'material' => [
                'name' => 'Kayu Tembak Grade Istimewa',
                'category' => 'Kayu Tembak',
                'unit' => 'kg',
            ],
        ]);

        $this->assertDatabaseHas('materials', [
            'name' => 'Kayu Tembak Grade Istimewa',
            'category' => 'Kayu Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
        ]);

        $material = Material::where('name', 'Kayu Tembak Grade Istimewa')->first();
        $this->assertNotNull($material->code);
        $this->assertStringStartsWith('MAT-', $material->code);
    }

    public function test_materials_index_complies_with_simplified_ui_and_real_category_filters(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        Material::create([
            'code' => 'MAT-REAL-001',
            'name' => 'Bahan Kayu Khusus',
            'category' => 'Kategori Asli Database',
            'unit' => 'kg',
            'stock_quantity' => 10.00,
            'minimum_stock' => 2.00,
            'unit_cost' => 50000.00,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('production.materials.index'));
        $response->assertStatus(200);

        // 1. KPI cards removed
        $response->assertDontSee('Total Jenis Bahan');
        $response->assertDontSee('Estimasi Nilai Bahan');
        $response->assertDontSee('Total valuasi inventaris bahan');

        // 2. Real category filter: only real categories, no unseeded default categories
        $response->assertSee('Kategori Asli Database');
        $response->assertDontSee('Ampas Finishing');

        // 3 & 4. Redundant buttons in Riwayat section removed
        $response->assertDontSee(route('production.materials.export-logs'));
    }

    public function test_production_user_can_view_create_receipt_form_with_default_categories(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $this->actingAs($user);

        $response = $this->get(route('production.materials.create-receipt'));
        $response->assertStatus(200);

        // 1. Text label is 'Nomor Unik Sistem'
        $response->assertSee('Nomor Unik Sistem');
        $response->assertDontSee('No. Bukti Penerimaan');

        // 2. Default categories are present in the response
        $response->assertSee('KCB');
        $response->assertSee('Getah');
        $response->assertSee('Metanol');
        $response->assertSee('Pewarna');
    }

    public function test_production_user_can_delete_material(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'MAT-DEL-001',
            'name' => 'Bahan Untuk Dihapus',
            'category' => 'Bahan Uji',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 0.00,
            'unit_cost' => 10000.00,
        ]);

        $this->actingAs($user);

        // Verify "Hapus Bahan" button exists in the edit modal
        $indexResponse = $this->get(route('production.materials.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Hapus Bahan');
        $indexResponse->assertSee('deleteMaterialForm');

        // Execute delete
        $response = $this->delete(route('production.materials.destroy', $material));
        $response->assertRedirect(route('production.materials.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('materials', [
            'id' => $material->id,
            'code' => 'MAT-DEL-001',
        ]);
    }

    public function test_production_user_can_view_dryings_index_without_kpi_cards_and_with_initiation_button(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'KCB-001',
            'name' => 'Bahan Baku KCB',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 100000.00,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('production.dryings.index'));
        $response->assertStatus(200);

        // Verification of header initiation button
        $response->assertSee('Inisiasi Jemur Bahan');
        $response->assertSee(route('production.dryings.create'));

        // Verification that KPI cards and ready list table are removed
        $response->assertDontSee('Total Proses Jemur');
        $response->assertDontSee('Total Penyusutan');
        $response->assertDontSee('Riwayat pengeringan tercatat');
        $response->assertDontSee('Bahan Baku Siap Jemur');
        $response->assertDontSee('Mulai Jemur');

        // Verification that drying history and active panels are displayed
        $response->assertSee('Proses Jemur Berjalan');
        $response->assertSee('Riwayat Proses Jemur');
    }

    public function test_production_user_can_access_drying_create_page_without_material_id_and_initiate(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'KCB-002',
            'name' => 'Bahan Baku KCB Super',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 35.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 80000.00,
        ]);

        $this->actingAs($user);

        // Direct access to create page without material_id query param
        $createResponse = $this->get(route('production.dryings.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Inisiasi Jemur Bahan Baku');
        $createResponse->assertSee('Pilih Bahan Baku (Khusus KCB)');
        $createResponse->assertSee('KCB-002');

        // Submit form
        $postResponse = $this->post(route('production.dryings.store'), [
            'material_id' => $material->id,
            'start_date' => now()->toDateString(),
            'pic_name' => 'Budi Operator',
            'notes' => 'Pengeringan batch pagi',
        ]);

        $postResponse->assertRedirect(route('production.dryings.index'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('material_drying_logs', [
            'material_id' => $material->id,
            'pic_name' => 'Budi Operator',
            'status' => 'menunggu',
            'previous_weight' => 35.00,
        ]);
    }

    public function test_only_kcb_materials_can_be_initiated_for_drying(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $kcbMaterial = Material::create([
            'code' => 'KCB-VALID',
            'name' => 'Bahan Baku KCB Valid',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 20.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 50000.00,
        ]);
        $nonKcbMaterial = Material::create([
            'code' => 'GETAH-INVALID',
            'name' => 'Bahan Getah Alami',
            'category' => 'Getah',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 30000.00,
        ]);

        $this->actingAs($user);

        // In create form, only KCB material is present in the select options
        $createResponse = $this->get(route('production.dryings.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('KCB-VALID');
        $createResponse->assertDontSee('GETAH-INVALID');

        // Trying to pass non-KCB material_id query param directly redirects with error
        $paramResponse = $this->get(route('production.dryings.create', ['material_id' => $nonKcbMaterial->id]));
        $paramResponse->assertRedirect(route('production.dryings.index'));
        $paramResponse->assertSessionHasErrors();

        // Submitting non-KCB material_id fails validation
        $postResponse = $this->post(route('production.dryings.store'), [
            'material_id' => $nonKcbMaterial->id,
            'start_date' => now()->toDateString(),
            'pic_name' => 'Operator',
        ]);

        $postResponse->assertSessionHasErrors('material_id');
        $this->assertDatabaseMissing('material_drying_logs', [
            'material_id' => $nonKcbMaterial->id,
        ]);
    }

    public function test_production_user_can_submit_drying_report_with_drying_duration_hours(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'KCB-DRY-01',
            'name' => 'KCB Bahan Jemur',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 100.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);

        $log = $material->dryingLogs()->create([
            'start_date' => now()->subDays(2)->toDateString(),
            'start_time' => '08:00',
            'previous_weight' => 100.00,
            'pic_name' => 'Operator Awal',
            'status' => 'menunggu',
        ]);

        $this->actingAs($user);

        // Check report view has "Waktu Jemur" and "Waktu Angkat" inputs
        $reportPageResponse = $this->get(route('production.dryings.report.create', $log));
        $reportPageResponse->assertStatus(200);
        $reportPageResponse->assertSee('Waktu Jemur');
        $reportPageResponse->assertSee('Waktu Angkat');
        $reportPageResponse->assertSee('name="start_time"', false);
        $reportPageResponse->assertSee('name="end_time"', false);
        $reportPageResponse->assertSee('Hasil Konversi Lama Jemur');

        // Submit report with start_time (08:00) and end_time (16:30) => converts to 8.5 hours
        $response = $this->post(route('production.dryings.report', $log), [
            'end_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '16:30',
            'new_weight' => 92.50,
            'pic_name' => 'Operator Lapor',
            'notes' => 'Selesai jemur dari 08:00 sampai 16:30',
        ]);

        $response->assertRedirect(route('production.dryings.index'));
        $response->assertSessionHas('success');

        // Verify log database record has start_time, end_time, and converted drying_hours (8.50)
        $this->assertDatabaseHas('material_drying_logs', [
            'id' => $log->id,
            'start_time' => '08:00',
            'end_time' => '16:30',
            'drying_hours' => 8.50,
            'new_weight' => 92.50,
            'shrinkage_weight' => 7.50,
            'status' => 'selesai',
            'pic_name' => 'Operator Lapor',
        ]);

        // Verify material stock updated
        $this->assertEquals(92.50, $material->fresh()->stock_quantity);

        // Verify index view displays converted drying hours with time range
        $indexResponse = $this->get(route('production.dryings.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('8.5 Jam');
        $indexResponse->assertSee('08:00 - 16:30');
    }

    public function test_active_drying_processes_appear_in_proses_jemur_berjalan_panel(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'KCB-ACTIVE-01',
            'name' => 'KCB Aktif Jemur',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 80.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);

        $activeLog = $material->dryingLogs()->create([
            'start_date' => now()->toDateString(),
            'start_time' => '08:30',
            'previous_weight' => 80.00,
            'pic_name' => 'Operator Lapangan',
            'status' => 'menunggu',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('production.dryings.index'));
        $response->assertStatus(200);

        // Verification of "Proses Jemur Berjalan" section content
        $response->assertSee('Proses Jemur Berjalan');
        $response->assertSee('KCB-ACTIVE-01');
        $response->assertSee('KCB Aktif Jemur');
        $response->assertSee('08:30 WIB');
        $response->assertSee('Operator Lapangan');
        $response->assertSee('Lapor Selesai');
        $response->assertSee(route('production.dryings.report.create', $activeLog->id));
    }

    public function test_materials_index_displays_process_history_for_materials(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $material = Material::create([
            'code' => 'MAT-PROC-01',
            'name' => 'Bahan Baku KCB Proses',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 45.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 60000.00,
        ]);

        $material->dryingLogs()->create([
            'start_date' => now()->toDateString(),
            'start_time' => '07:30',
            'previous_weight' => 50.00,
            'new_weight' => 45.00,
            'shrinkage_weight' => 5.00,
            'drying_hours' => 6.50,
            'pic_name' => 'Budi Jemur',
            'status' => 'selesai',
            'notes' => 'Pengeringan tahap awal',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('production.materials.index'));
        $response->assertStatus(200);

        // Verification of column and process button
        $response->assertSee('Riwayat Proses');
        $response->assertSee('1 Proses');
        $response->assertSee('processDetail'.$material->id);
        $response->assertSee('Jemur Bahan');
        $response->assertSee('6.5 Jam');
        $response->assertSee('Budi Jemur');
    }

    public function test_sort_create_only_displays_kcb_materials_without_active_process(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);

        // Material 1: Valid KCB with stock, no active process
        $validMaterial = Material::create([
            'code' => 'KCB-READY-01',
            'name' => 'KCB Kayu Siap Sortir',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 100.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);

        // Material 2: Non-KCB category with stock
        $nonKcbMaterial = Material::create([
            'code' => 'NON-KCB-01',
            'name' => 'Bahan Sortir Lainnya',
            'category' => 'Bahan Sortir',
            'unit' => 'kg',
            'stock_quantity' => 80.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 40000.00,
        ]);

        // Material 3: KCB with active drying log ('menunggu')
        $dryingMaterial = Material::create([
            'code' => 'KCB-DRY-01',
            'name' => 'KCB Sedang Jemur',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 60.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);
        $dryingMaterial->dryingLogs()->create([
            'start_date' => now()->toDateString(),
            'start_time' => '08:00',
            'previous_weight' => 60.00,
            'status' => 'menunggu',
            'pic_name' => 'Petugas Jemur',
        ]);

        // Material 4: KCB with active sorting ('in_progress')
        $sortingMaterial = Material::create([
            'code' => 'KCB-SORT-01',
            'name' => 'KCB Sedang Sortir',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 70.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);
        $sortingMaterial->sortBatches()->create([
            'sort_code' => 'SRT-ACTIVE-01',
            'status' => 'in_progress',
            'initial_weight' => 30.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Petugas Sortir',
        ]);

        // Material 5: KCB with 0 stock
        $zeroStockMaterial = Material::create([
            'code' => 'KCB-ZERO-01',
            'name' => 'KCB Habis Stok',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('production.sorts.create'));
        $response->assertStatus(200);

        // Assert valid material is present in the selection
        $response->assertSee('KCB-READY-01');
        $response->assertSee('KCB Kayu Siap Sortir');

        // Assert non-KCB and busy materials are NOT present in the selection
        $response->assertDontSee('NON-KCB-01');
        $response->assertDontSee('Bahan Sortir Lainnya');
        $response->assertDontSee('KCB-DRY-01');
        $response->assertDontSee('KCB Sedang Jemur');
        $response->assertDontSee('KCB-SORT-01');
        $response->assertDontSee('KCB Sedang Sortir');
        $response->assertDontSee('KCB-ZERO-01');
        $response->assertDontSee('KCB Habis Stok');
    }

    public function test_sort_store_validates_kcb_and_active_process_restriction(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);

        $validMaterial = Material::create([
            'code' => 'KCB-VALID-01',
            'name' => 'KCB Siap Pakai',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 55000.00,
        ]);

        $nonKcb = Material::create([
            'code' => 'NON-KCB-02',
            'name' => 'Minyak Bahan',
            'category' => 'Minyak',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 55000.00,
        ]);

        $activeProcessMat = Material::create([
            'code' => 'KCB-BUSY-01',
            'name' => 'KCB Sedang Jemur',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 55000.00,
        ]);
        $activeProcessMat->dryingLogs()->create([
            'start_date' => now()->toDateString(),
            'start_time' => '09:00',
            'previous_weight' => 50.00,
            'status' => 'menunggu',
            'pic_name' => 'Petugas',
        ]);

        $this->actingAs($user);

        // 1. Non-KCB must be rejected
        $resNonKcb = $this->post(route('production.sorts.store'), [
            'source_material_id' => $nonKcb->id,
            'initial_weight' => 10.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Tester',
        ]);
        $resNonKcb->assertSessionHasErrors(['source_material_id']);

        // 2. Active process material must be rejected
        $resBusy = $this->post(route('production.sorts.store'), [
            'source_material_id' => $activeProcessMat->id,
            'initial_weight' => 10.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Tester',
        ]);
        $resBusy->assertSessionHasErrors(['source_material_id']);

        // 3. Valid KCB material succeeds
        $resValid = $this->post(route('production.sorts.store'), [
            'source_material_id' => $validMaterial->id,
            'initial_weight' => 20.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Tester',
        ]);
        $resValid->assertSessionHasNoErrors();
        $resValid->assertRedirect();

        $this->assertEquals(30.00, $validMaterial->fresh()->stock_quantity);
    }

    public function test_user_can_save_draft_sort_report_and_continue_later(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);

        $source = Material::create([
            'code' => 'KCB-SRC-01',
            'name' => 'KCB Kayu Bahan',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 100.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);

        $target = Material::create([
            'code' => 'TMB-GRADE-A',
            'name' => 'Kayu Tembak Grade A',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 60000.00,
        ]);

        $sortBatch = $source->sortBatches()->create([
            'sort_code' => 'SRT-DRAFT-01',
            'status' => 'in_progress',
            'initial_weight' => 50.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Inisiator Sortir',
        ]);
        $source->decrement('stock_quantity', 50.00);

        $this->actingAs($user);

        // 1. Simpan dan Lanjutkan Nanti (action = draft)
        $draftRes = $this->post(route('production.sorts.store-report', $sortBatch), [
            'action' => 'draft',
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Petugas Pagi',
            'notes' => 'Sortir baru separuh jalan',
            'items' => [
                1 => [
                    'target_material_id' => $target->id,
                    'result_weight' => 15.00,
                    'notes' => 'Porsi tahap 1',
                ],
            ],
        ]);

        $draftRes->assertRedirect(route('production.sorts.show', $sortBatch));
        $draftRes->assertSessionHas('success');

        $sortBatch->refresh();
        $this->assertEquals('in_progress', $sortBatch->status);
        $this->assertEquals('Petugas Pagi', $sortBatch->report_pic_name);
        $this->assertEquals('Sortir baru separuh jalan', $sortBatch->notes);
        $this->assertEquals(1, $sortBatch->items()->count());
        $this->assertEquals(15.00, (float) $sortBatch->items()->first()->result_weight);

        // Pastikan stok gudang target BELUM bertambah saat masih draft
        $this->assertEquals(0.00, (float) $target->fresh()->stock_quantity);

        // Pastikan halaman show menampilkan data draft yang tersimpan
        $showRes = $this->get(route('production.sorts.show', $sortBatch));
        $showRes->assertStatus(200);
        $showRes->assertSee('Petugas Pagi');
        $showRes->assertSee('Sortir baru separuh jalan');
        $showRes->assertSee('Simpan & Lanjutkan Nanti', false);
        $showRes->assertSee('Simpan Laporan Sortir');

        // 2. Finalisasi Laporan Sortir (action = complete)
        $completeRes = $this->post(route('production.sorts.store-report', $sortBatch), [
            'action' => 'complete',
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Petugas Sore',
            'notes' => 'Semua selesai disortir',
            'items' => [
                1 => [
                    'target_material_id' => $target->id,
                    'result_weight' => 45.00,
                    'notes' => 'Hasil total',
                ],
            ],
        ]);

        $completeRes->assertRedirect(route('production.sorts.show', $sortBatch));
        $sortBatch->refresh();
        $this->assertEquals('completed', $sortBatch->status);
        $this->assertEquals(45.00, (float) $sortBatch->dried_weight);
        $this->assertEquals(5.00, (float) $sortBatch->drying_loss_weight);

        // Pastikan stok target sekarang bertambah
        $this->assertEquals(45.00, (float) $target->fresh()->stock_quantity);
    }

    public function test_sort_report_handles_returned_raw_material_and_calculates_real_shrinkage(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);

        // Source raw material KCB (initial 100 kg, after taking 100 kg remains 0 kg)
        $source = Material::create([
            'code' => 'KCB-BAL-01',
            'name' => 'KCB Kayu Utuh',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000.00,
        ]);

        $target = Material::create([
            'code' => 'TMB-BAL-01',
            'name' => 'Kayu Tembak Grade A',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 60000.00,
        ]);

        $sortBatch = $source->sortBatches()->create([
            'sort_code' => 'SRT-BAL-01',
            'status' => 'in_progress',
            'initial_weight' => 100.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Inisiator',
        ]);

        $this->actingAs($user);

        // 1. Submit report with excess weight (e.g. 60 + 50 = 110 > 100) must fail
        $exceedRes = $this->post(route('production.sorts.store-report', $sortBatch), [
            'action' => 'complete',
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Petugas Sortir',
            'returned_weight' => 50.00,
            'items' => [
                1 => [
                    'target_material_id' => $target->id,
                    'result_weight' => 60.00,
                ],
            ],
        ]);
        $exceedRes->assertSessionHasErrors(['report']);

        // 2. Submit valid report:
        // Initial = 100 kg
        // Hasil sortir = 55 kg
        // Returned to source raw material = 40 kg
        // Expected shrinkage = 100 - (55 + 40) = 5 kg
        $validRes = $this->post(route('production.sorts.store-report', $sortBatch), [
            'action' => 'complete',
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Petugas Sortir',
            'returned_weight' => 40.00,
            'notes' => '40 kg kayu mentah dikembalikan ke gudang, susut serbuk 5 kg',
            'items' => [
                1 => [
                    'target_material_id' => $target->id,
                    'result_weight' => 55.00,
                ],
            ],
        ]);

        $validRes->assertRedirect(route('production.sorts.show', $sortBatch));
        $sortBatch->refresh();

        $this->assertEquals('completed', $sortBatch->status);
        $this->assertEquals(55.00, (float) $sortBatch->dried_weight);
        $this->assertEquals(40.00, (float) $sortBatch->returned_weight);
        $this->assertEquals(5.00, (float) $sortBatch->drying_loss_weight);

        // Verify source material stock returned +40 kg
        $this->assertEquals(40.00, (float) $source->fresh()->stock_quantity);

        // Verify target material stock received +55 kg
        $this->assertEquals(55.00, (float) $target->fresh()->stock_quantity);

        // Verify MaterialLog for returned source material
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $source->id,
            'type' => 'in',
            'quantity' => 40.00,
            'reference_number' => $sortBatch->sort_code,
        ]);

        // Verify show page displays returned weight and shrinkage
        $showRes = $this->get(route('production.sorts.show', $sortBatch));
        $showRes->assertStatus(200);
        $showRes->assertSee('40.0 kg');
        $showRes->assertSee('55.0 kg');
        $showRes->assertSee('5.0 kg');
        $showRes->assertSee('Dikembalikan ke Bahan Asal');
        $showRes->assertSee('Penyusutan Riil (Susut)');
    }

    public function test_sort_report_rejects_self_allocation_and_enforces_bahan_tembak_or_potong_ukir(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);

        $source = Material::create([
            'code' => 'KCB-RESTRICT-01',
            'name' => 'KCB Kayu Induk Khusus',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 10.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 50000.00,
        ]);

        $nonAllowedMaterial = Material::create([
            'code' => 'KCB-OTHER-01',
            'name' => 'KCB Kayu Lainnya Di Luar Target',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 50000.00,
        ]);

        $validTembak = Material::create([
            'code' => 'TMB-VALID-01',
            'name' => 'Bahan Kayu Tembak Super',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 50000.00,
        ]);

        $validPotongUkir = Material::create([
            'code' => 'UKR-VALID-01',
            'name' => 'Bahan Kayu Ukir Kelas 1',
            'category' => 'Bahan Potong Ukir',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 50000.00,
        ]);

        $sortBatch = $source->sortBatches()->create([
            'sort_code' => 'SRT-RESTRICT-01',
            'status' => 'in_progress',
            'initial_weight' => 50.00,
            'sort_date' => now()->toDateString(),
            'pic_name' => 'Inisiator',
        ]);

        $this->actingAs($user);

        // Verify show view excludes source material and only displays Bahan Tembak / Bahan Potong Ukir
        $showRes = $this->get(route('production.sorts.show', $sortBatch));
        $showRes->assertStatus(200);
        $showRes->assertDontSee($nonAllowedMaterial->name);
        $showRes->assertSee('Bahan Kayu Tembak Super');
        $showRes->assertSee('Bahan Kayu Ukir Kelas 1');
        $showRes->assertSee('Bahan Tembak');
        $showRes->assertSee('Bahan Potong Ukir');

        // 1. Try to allocate back to itself -> must fail
        $resSelf = $this->post(route('production.sorts.store-report', $sortBatch), [
            'action' => 'complete',
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Petugas',
            'items' => [
                1 => [
                    'target_material_id' => $source->id,
                    'result_weight' => 20.00,
                ],
            ],
        ]);
        $resSelf->assertSessionHasErrors(['items.1.target_material_id']);

        // 2. Try to allocate to non-allowed category (e.g. KCB) -> must fail
        $resCategory = $this->post(route('production.sorts.store-report', $sortBatch), [
            'action' => 'complete',
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Petugas',
            'items' => [
                1 => [
                    'target_material_id' => $nonAllowedMaterial->id,
                    'result_weight' => 20.00,
                ],
            ],
        ]);
        $resCategory->assertSessionHasErrors(['items.1.target_material_id']);

        // 3. Valid allocation to Bahan Tembak and Bahan Potong Ukir -> must succeed
        $resValid = $this->post(route('production.sorts.store-report', $sortBatch), [
            'action' => 'complete',
            'report_date' => now()->toDateString(),
            'report_pic_name' => 'Petugas',
            'returned_weight' => 10.00,
            'items' => [
                1 => [
                    'target_material_id' => $validTembak->id,
                    'result_weight' => 25.00,
                ],
                2 => [
                    'target_material_id' => $validPotongUkir->id,
                    'result_weight' => 12.00,
                ],
            ],
        ]);
        $resValid->assertSessionHasNoErrors();
        $resValid->assertRedirect(route('production.sorts.show', $sortBatch));

        $sortBatch->refresh();
        $this->assertEquals('completed', $sortBatch->status);
        $this->assertEquals(37.00, (float) $sortBatch->dried_weight);
        $this->assertEquals(10.00, (float) $sortBatch->returned_weight);
        $this->assertEquals(3.00, (float) $sortBatch->drying_loss_weight); // 50 - 37 - 10 = 3 kg

        $this->assertEquals(25.00, (float) $validTembak->fresh()->stock_quantity);
        $this->assertEquals(12.00, (float) $validPotongUkir->fresh()->stock_quantity);
    }

    public function test_drying_create_page_accessible_and_shows_empty_state_when_no_kcb_materials_available(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $this->actingAs($user);

        // Ensure no KCB materials exist
        Material::where('category', 'KCB')->delete();

        $response = $this->get(route('production.dryings.create'));

        $response->assertStatus(200);
        $response->assertSee('Inisiasi Jemur Bahan Baku');
        $response->assertSee('Tidak ada bahan baku berkategori KCB dengan stok tersedia yang siap dijemur.');
        $response->assertSee('Tidak ada bahan KCB yang siap dijemur');
    }

    public function test_drying_create_excludes_materials_with_active_processes_and_zero_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $this->actingAs($user);

        // 1. Ready KCB material
        $readyMat = Material::create([
            'code' => 'KCB-READY',
            'name' => 'KCB Siap Jemur',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 25.00,
            'unit_cost' => 50000,
            'minimum_stock' => 0,
        ]);

        // 2. KCB currently being dried
        $dryingMat = Material::create([
            'code' => 'KCB-DRYING',
            'name' => 'KCB Sedang Dijemur',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 20.00,
            'unit_cost' => 50000,
            'minimum_stock' => 0,
        ]);
        $dryingMat->dryingLogs()->create([
            'start_date' => now()->toDateString(),
            'start_time' => '08:00',
            'previous_weight' => 20.00,
            'pic_name' => 'Operator',
            'status' => 'menunggu',
        ]);

        // 3. KCB with 0 stock
        $zeroStockMat = Material::create([
            'code' => 'KCB-ZERO',
            'name' => 'KCB Stok Kosong',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 0,
            'unit_cost' => 50000,
            'minimum_stock' => 0,
        ]);

        $response = $this->get(route('production.dryings.create'));
        $response->assertStatus(200);
        $response->assertSee('KCB-READY');
        $response->assertDontSee('KCB-DRYING');
        $response->assertDontSee('KCB-ZERO');

        // Submitting drying for material in active process fails validation
        $postResponse = $this->post(route('production.dryings.store'), [
            'material_id' => $dryingMat->id,
            'start_date' => now()->toDateString(),
            'pic_name' => 'PIC Test',
        ]);
        $postResponse->assertSessionHasErrors(['material_id']);
    }
}
