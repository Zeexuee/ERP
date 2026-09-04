<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\User;
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
            'storage_location' => 'Rak A-01 Sentul',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('production.materials.index'));
        $response->assertStatus(200);
        $response->assertSee('Barang Gudang');
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

        // Verify stock incremented: 10 + 15 = 25
        $material->refresh();
        $this->assertEquals(25.00, (float) $material->stock_quantity);
        $this->assertEquals(1250000.00, (float) $material->unit_cost);
    }

    public function test_sales_user_cannot_access_production_warehouse_directly(): void
    {
        $salesUser = User::factory()->create(['role' => UserRole::SALES]);
        $this->actingAs($salesUser);

        $response = $this->get(route('production.materials.index'));
        $response->assertStatus(403);
    }
}
