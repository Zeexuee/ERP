<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\MaterialCarveBatch;
use App\Models\MaterialDryingLog;
use App\Models\MaterialReceipt;
use App\Models\MaterialSortBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialProcessHistoryAndCarveTest extends TestCase
{
    use RefreshDatabase;

    private function productionUser(): User
    {
        return User::factory()->create(['role' => UserRole::PRODUCTION]);
    }

    public function test_carve_create_form_only_shows_materials_with_category_bahan_potong_ukir(): void
    {
        $user = $this->productionUser();

        $validMaterial = Material::create([
            'code' => 'MAT-CRV-01',
            'name' => 'Kayu Gaharu Siap Ukir',
            'category' => 'Bahan Potong Ukir',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 100000,
        ]);

        $invalidMaterial = Material::create([
            'code' => 'MAT-KCB-01',
            'name' => 'Kayu Campuran Biasa',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 100.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 50000,
        ]);

        $response = $this->actingAs($user)->get(route('production.carves.create'));

        $response->assertOk();
        $response->assertSee('Kayu Gaharu Siap Ukir');
        $response->assertSee('MAT-CRV-01');
        $response->assertDontSee('Kayu Campuran Biasa');
        $response->assertDontSee('MAT-KCB-01');
    }

    public function test_carve_store_rejects_material_not_in_bahan_potong_ukir_category(): void
    {
        $user = $this->productionUser();

        $invalidMaterial = Material::create([
            'code' => 'MAT-KCB-02',
            'name' => 'Kayu Campuran Biasa 2',
            'category' => 'KCB',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 50000,
        ]);

        $response = $this->actingAs($user)->post(route('production.carves.store'), [
            'source_material_id' => $invalidMaterial->id,
            'initial_weight' => 10.00,
            'carve_date' => now()->toDateString(),
            'pic_name' => 'Ahmad Pengukir',
        ]);

        $response->assertSessionHasErrors('source_material_id');
        $this->assertDatabaseCount('material_carve_batches', 0);
    }

    public function test_carve_store_successfully_initiates_with_valid_bahan_potong_ukir(): void
    {
        $user = $this->productionUser();

        $validMaterial = Material::create([
            'code' => 'MAT-CRV-02',
            'name' => 'Kayu Balok Ukir A',
            'category' => 'Bahan Potong Ukir',
            'unit' => 'kg',
            'stock_quantity' => 40.00,
            'minimum_stock' => 5.00,
            'unit_cost' => 120000,
        ]);

        $response = $this->actingAs($user)->post(route('production.carves.store'), [
            'source_material_id' => $validMaterial->id,
            'initial_weight' => 15.00,
            'carve_date' => now()->toDateString(),
            'pic_name' => 'Ahmad Pengukir',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('material_carve_batches', [
            'source_material_id' => $validMaterial->id,
            'initial_weight' => 15.00,
            'status' => 'in_progress',
            'pic_name' => 'Ahmad Pengukir',
        ]);

        $validMaterial->refresh();
        $this->assertEquals(25.00, $validMaterial->stock_quantity);
    }

    public function test_material_warehouse_process_history_includes_receipt_drying_and_sort(): void
    {
        $user = $this->productionUser();

        $material = Material::create([
            'code' => 'MAT-MULTI-01',
            'name' => 'Kayu Gaharu Multi Alur',
            'category' => 'Bahan Potong Ukir',
            'unit' => 'kg',
            'stock_quantity' => 80.00,
            'minimum_stock' => 10.00,
            'unit_cost' => 100000,
        ]);

        // 1. Barang Masuk
        MaterialReceipt::create([
            'receipt_number' => 'IN-2026-9901',
            'material_id' => $material->id,
            'quantity' => 100.00,
            'unit' => 'kg',
            'source_or_supplier' => 'Pemasok Hutan Asli',
            'received_date' => now()->subDays(5)->toDateString(),
            'received_by' => 'Staff Gudang',
        ]);

        // 2. Jemur Bahan
        MaterialDryingLog::create([
            'material_id' => $material->id,
            'start_date' => now()->subDays(3)->toDateString(),
            'start_time' => '08:00',
            'end_date' => now()->subDays(2)->toDateString(),
            'end_time' => '16:00',
            'drying_hours' => 8,
            'previous_weight' => 100.00,
            'new_weight' => 95.00,
            'shrinkage_weight' => 5.00,
            'status' => 'selesai',
            'pic_name' => 'Petugas Jemur',
        ]);

        // 3. Sortir Kayu
        MaterialSortBatch::create([
            'sort_code' => 'SRT-202609-9901',
            'status' => 'in_progress',
            'source_material_id' => $material->id,
            'initial_weight' => 50.00,
            'sort_date' => now()->subDays(1)->toDateString(),
            'pic_name' => 'Petugas Sortir',
        ]);

        $response = $this->actingAs($user)->get(route('production.materials.index'));

        $response->assertOk();
        $response->assertSee('3 Proses');
        $response->assertSee('Barang Masuk');
        $response->assertSee('IN-2026-9901');
        $response->assertSee('+100.00 kg');
        $response->assertSee('Pemasok Hutan Asli');

        $response->assertSee('Jemur Bahan');
        $response->assertSee('Petugas Jemur');
        $response->assertSee('100.00 kg → 95.00 kg');

        $response->assertSee('Sortir Kayu');
        $response->assertSee('SRT-202609-9901');
        $response->assertSee('Petugas Sortir');
        $response->assertSee('Awal: 50.00 kg');
    }
}
