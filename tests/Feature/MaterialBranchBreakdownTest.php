<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\MaterialBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialBranchBreakdownTest extends TestCase
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

    public function test_warehouse_index_lists_every_branch_making_up_a_material_total(): void
    {
        $material = Material::create([
            'code' => 'MAT-BRK-01',
            'name' => 'Kayu Tembak Campuran',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 30.00,
            'unit_cost' => 400000,
        ]);

        MaterialBranch::create([
            'material_id' => $material->id,
            'branch_code' => 'BR-202609-0001',
            'quantity' => 18.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
            'source_reference' => 'TBK-202609-0001',
        ]);
        MaterialBranch::create([
            'material_id' => $material->id,
            'branch_code' => 'BR-202609-0002',
            'quantity' => 12.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
            'source_reference' => 'TBK-202609-0002',
        ]);

        $response = $this->actingAs($this->productionUser)->get(route('production.materials.index'));

        $response->assertOk();
        $response->assertSee('Rincian Branch');
        $response->assertSee('BR-202609-0001');
        $response->assertSee('BR-202609-0002');
        $response->assertSee('TBK-202609-0001');
        $response->assertSee('TBK-202609-0002');
        // Porsi tiap branch terhadap total 30 kg.
        $response->assertSee('60.0%');
        $response->assertSee('40.0%');
        // Barang yang memuat lebih dari satu branch ditandai gabungan.
        $response->assertSee('2 branch · gabungan', false);

        $material->load('branches');
        $this->assertSame(30.0, $material->branched_quantity);
        $this->assertSame(0.0, $material->unbranched_quantity);
        $this->assertFalse($material->hasBranchDiscrepancy());
        $this->assertStringContainsString('(Gabungan)', $material->branch_label);
    }

    public function test_warehouse_index_surfaces_stock_that_has_no_branch_identity_yet(): void
    {
        $material = Material::create([
            'code' => 'MAT-BRK-02',
            'name' => 'Getah Belum Teridentifikasi',
            'category' => 'Getah',
            'unit' => 'kg',
            'stock_quantity' => 25.00,
            'unit_cost' => 800000,
        ]);

        MaterialBranch::create([
            'material_id' => $material->id,
            'branch_code' => 'BR-202609-0003',
            'quantity' => 10.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
        ]);

        $response = $this->actingAs($this->productionUser)->get(route('production.materials.index'));

        $response->assertOk();
        $response->assertSee('BR-202609-0003');
        $response->assertSee('Tanpa Branch');
        $response->assertSee('Belum Teridentifikasi');

        $material->load('branches');
        $this->assertSame(10.0, $material->branched_quantity);
        $this->assertSame(15.0, $material->unbranched_quantity);
        $this->assertFalse($material->hasBranchDiscrepancy());
    }

    public function test_branch_ledger_exceeding_warehouse_stock_is_flagged_for_review(): void
    {
        $material = Material::create([
            'code' => 'MAT-BRK-03',
            'name' => 'Bahan Selisih',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 5.00,
            'unit_cost' => 100000,
        ]);

        MaterialBranch::create([
            'material_id' => $material->id,
            'branch_code' => 'BR-202609-0004',
            'quantity' => 9.00,
            'source_type' => MaterialBranch::SOURCE_TEMBAK,
        ]);

        $material->load('branches');
        $this->assertTrue($material->hasBranchDiscrepancy());
        $this->assertSame(0.0, $material->unbranched_quantity);

        $response = $this->actingAs($this->productionUser)->get(route('production.materials.index'));
        $response->assertOk();
        $response->assertSee('melebihi stok gudang');
    }

    public function test_zero_stock_material_reports_no_branch(): void
    {
        Material::create([
            'code' => 'MAT-BRK-04',
            'name' => 'Bahan Habis',
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0.00,
            'unit_cost' => 0,
        ]);

        $response = $this->actingAs($this->productionUser)->get(route('production.materials.index'));

        $response->assertOk();
        $response->assertSee('Tanpa branch');
        $response->assertSee('Belum ada stok pada barang ini.');
    }
}
