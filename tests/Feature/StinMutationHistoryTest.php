<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WarehouseBranch;
use App\Models\WarehouseItem;
use App\Models\WarehouseStockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StinMutationHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $stinUser;

    private WarehouseBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stinUser = User::factory()->create([
            'role' => UserRole::STIN,
            'name' => 'Petugas STIN Mutasi',
        ]);

        $this->branch = WarehouseBranch::create([
            'code' => 'WH-PST',
            'name' => 'Gudang Pusat',
            'is_active' => true,
        ]);
    }

    public function test_stin_user_can_access_mutation_history_page(): void
    {
        $item = WarehouseItem::create([
            'warehouse_branch_id' => $this->branch->id,
            'name' => 'Kopi Arabika Toraja 250g',
            'category' => 'Biji Kopi',
            'current_stock' => 50,
            'unit' => 'pack',
            'unit_price' => 75000,
            'sku' => 'SKU-KOP-001',
            'is_active' => true,
        ]);

        $item->addStock(
            quantity: 20,
            notes: 'Stok masuk awal dari supplier',
            picName: 'Ahmad Gudang'
        );

        $response = $this->actingAs($this->stinUser)->get(route('stin.mutations.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Mutasi Barang');
        $response->assertSee('Pilih Bulan');
        $response->assertSee('Kopi Arabika Toraja 250g');
        $response->assertSee('SKU-KOP-001');
        $response->assertSee('Ahmad Gudang');
        $response->assertSee('+20 pack');
        $response->assertSee('Detail');
    }

    public function test_stin_user_can_view_single_mutation_detail(): void
    {
        $item = WarehouseItem::create([
            'warehouse_branch_id' => $this->branch->id,
            'name' => 'Kopi Arabika Toraja 250g',
            'category' => 'Biji Kopi',
            'current_stock' => 50,
            'unit' => 'pack',
            'sku' => 'SKU-KOP-001',
            'is_active' => true,
        ]);

        $item->removeStock(
            quantity: 2,
            notes: 'Keluar 2 unit via barcode scanner',
            picName: 'Budi Petugas'
        );

        $movement = WarehouseStockMovement::latest('id')->firstOrFail();

        $response = $this->actingAs($this->stinUser)->getJson(route('stin.mutations.show', $movement));

        $response->assertOk();
        $response->assertJson([
            'type' => 'out',
            'quantity' => 2,
            'stock_before' => 50,
            'stock_after' => 48,
            'item' => [
                'name' => 'Kopi Arabika Toraja 250g',
                'sku' => 'SKU-KOP-001',
            ],
            'pic_name' => 'Budi Petugas',
            'notes' => 'Keluar 2 unit via barcode scanner',
        ]);
    }

    public function test_sales_role_cannot_access_mutation_history_page(): void
    {
        $salesUser = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $response = $this->actingAs($salesUser)->get(route('stin.mutations.index'));

        $response->assertForbidden();
    }

    public function test_mutations_can_be_filtered_by_month_category(): void
    {
        $item = WarehouseItem::create([
            'warehouse_branch_id' => $this->branch->id,
            'name' => 'Minyak Gaharu Super',
            'category' => 'Minyak Atsiri',
            'current_stock' => 100,
            'unit' => 'botol',
            'sku' => 'SKU-GHR-001',
            'is_active' => true,
        ]);

        // Buat mutasi di bulan September 2026
        WarehouseStockMovement::create([
            'warehouse_item_id' => $item->id,
            'warehouse_branch_id' => $this->branch->id,
            'type' => 'in',
            'quantity' => 40,
            'stock_before' => 0,
            'stock_after' => 40,
            'notes' => 'Pasokan September',
            'pic_name' => 'Rahmat',
            'user_id' => $this->stinUser->id,
            'movement_date' => '2026-09-15 10:00:00',
            'created_at' => '2026-09-15 10:00:00',
        ]);

        // Buat mutasi di bulan Oktober 2026
        WarehouseStockMovement::create([
            'warehouse_item_id' => $item->id,
            'warehouse_branch_id' => $this->branch->id,
            'type' => 'out',
            'quantity' => 15,
            'stock_before' => 40,
            'stock_after' => 25,
            'notes' => 'Pengeluaran Oktober via barcode',
            'pic_name' => 'Fathan',
            'user_id' => $this->stinUser->id,
            'movement_date' => '2026-10-10 14:30:00',
            'created_at' => '2026-10-10 14:30:00',
        ]);

        // Filter bulan September 2026
        $responseSep = $this->actingAs($this->stinUser)->get(route('stin.mutations.index', ['month' => '2026-09']));
        $responseSep->assertOk();
        $responseSep->assertSee('Pasokan September');
        $responseSep->assertDontSee('Pengeluaran Oktober via barcode');

        // Filter bulan Oktober 2026
        $responseOct = $this->actingAs($this->stinUser)->get(route('stin.mutations.index', ['month' => '2026-10']));
        $responseOct->assertOk();
        $responseOct->assertSee('Pengeluaran Oktober via barcode');
        $responseOct->assertDontSee('Pasokan September');
    }

    public function test_mutations_can_be_filtered_by_type_and_search(): void
    {
        $item1 = WarehouseItem::create([
            'warehouse_branch_id' => $this->branch->id,
            'name' => 'Teh Melati Premium',
            'category' => 'Teh',
            'current_stock' => 100,
            'unit' => 'box',
            'sku' => 'SKU-TEH-001',
            'is_active' => true,
        ]);

        $item2 = WarehouseItem::create([
            'warehouse_branch_id' => $this->branch->id,
            'name' => 'Kopi Robusta Lampung',
            'category' => 'Kopi',
            'current_stock' => 50,
            'unit' => 'kg',
            'sku' => 'SKU-KOP-002',
            'is_active' => true,
        ]);

        // Mutasi Masuk Teh
        WarehouseStockMovement::create([
            'warehouse_item_id' => $item1->id,
            'warehouse_branch_id' => $this->branch->id,
            'type' => 'in',
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
            'notes' => 'Restock teh',
            'pic_name' => 'Joko',
            'user_id' => $this->stinUser->id,
            'movement_date' => now(),
        ]);

        // Mutasi Keluar Kopi
        WarehouseStockMovement::create([
            'warehouse_item_id' => $item2->id,
            'warehouse_branch_id' => $this->branch->id,
            'type' => 'out',
            'quantity' => 10,
            'stock_before' => 50,
            'stock_after' => 40,
            'notes' => 'Pengiriman cabang',
            'pic_name' => 'Budi',
            'user_id' => $this->stinUser->id,
            'movement_date' => now(),
        ]);

        // Filter tipe 'out' (hanya kopi yang keluar)
        $responseType = $this->actingAs($this->stinUser)->get(route('stin.mutations.index', [
            'month' => now()->format('Y-m'),
            'type' => 'out',
        ]));
        $responseType->assertOk();
        $this->assertTrue($responseType->viewData('movements')->contains('item.name', 'Kopi Robusta Lampung'));
        $this->assertFalse($responseType->viewData('movements')->contains('item.name', 'Teh Melati Premium'));

        // Filter search 'Teh'
        $responseSearch = $this->actingAs($this->stinUser)->get(route('stin.mutations.index', [
            'month' => now()->format('Y-m'),
            'search' => 'Teh',
        ]));
        $responseSearch->assertOk();
        $this->assertTrue($responseSearch->viewData('movements')->contains('item.name', 'Teh Melati Premium'));
        $this->assertFalse($responseSearch->viewData('movements')->contains('item.name', 'Kopi Robusta Lampung'));
    }

    public function test_mutations_can_be_exported_to_csv(): void
    {
        $item = WarehouseItem::create([
            'warehouse_branch_id' => $this->branch->id,
            'name' => 'Produk Ekspor Test',
            'category' => 'Uji Coba',
            'current_stock' => 10,
            'unit' => 'pcs',
            'sku' => 'SKU-EXP-001',
            'is_active' => true,
        ]);

        $item->addStock(
            quantity: 10,
            notes: 'Mutasi untuk uji ekspor',
            picName: 'Petugas Ekspor'
        );

        $response = $this->actingAs($this->stinUser)->get(route('stin.mutations.export', [
            'month' => now()->format('Y-m'),
        ]));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('content-type'), 'text/csv'));
    }

    public function test_sidebar_contains_riwayat_mutasi_navigation_link(): void
    {
        $response = $this->actingAs($this->stinUser)->get(route('stin.dashboard'));

        $response->assertOk();
        $response->assertSee(route('stin.mutations.index'));
        $response->assertSee('Riwayat Mutasi');
    }
}
