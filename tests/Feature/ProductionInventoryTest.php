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
            'actor_by' => 'Ahmad Petugas Gudang',
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
}
