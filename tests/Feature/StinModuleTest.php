<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WarehouseBranch;
use App\Models\WarehouseItem;
use App\Models\WarehouseStockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StinModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_stin_user_can_access_stin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $response = $this->actingAs($user)->get(route('stin.dashboard'));

        $response->assertOk();
        $response->assertSee('STIN');
        $response->assertSee('Dashboard');
        $response->assertSee('Pelanggan');
        $response->assertSee('Pesanan');

        // Verify that chatty text, Divisi STIN nav, and old warehouse clutter are completely absent
        $response->assertDontSee('Divisi STIN');
        $response->assertDontSee('Selamat datang kembali');
        $response->assertDontSee('Pusat Kendali Divisi STIN');
        $response->assertDontSee('Barang Gudang');
        $response->assertDontSee('Scanner');
        $response->assertDontSee('Kode Dibuat Otomatis');
    }

    public function test_root_route_redirects_authenticated_stin_user_to_stin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('stin.dashboard'));
    }

    public function test_super_role_can_access_stin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SUPER_ROLE,
        ]);

        $response = $this->actingAs($user)->get(route('stin.dashboard'));

        $response->assertOk();
        $response->assertSee('STIN');
    }

    public function test_sales_role_cannot_access_stin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $response = $this->actingAs($user)->get(route('stin.dashboard'));

        $response->assertForbidden();
    }

    public function test_old_warehouse_routes_are_completely_removed(): void
    {
        $this->assertFalse(app('router')->has('warehouse.dashboard'));
        $this->assertFalse(app('router')->has('warehouse.items.index'));
        $this->assertFalse(app('router')->has('warehouse.items.create'));
        $this->assertFalse(app('router')->has('warehouse.scanner.index'));
    }

    public function test_stin_user_can_access_data_gudang_table(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'PST',
            'name' => 'Gudang Pusat',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Melati Mewah',
            'category' => 'Parfum Eksklusif',
            'current_stock' => 150,
            'unit' => 'botol',
            'unit_price' => 125000,
            'sku' => 'SKU-MELATI-001',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('stin.items.index'));

        $response->assertOk();
        $response->assertSee('Data Gudang');
        $response->assertSee('Nama Barang');
        $response->assertSee('Kategori Barang');
        $response->assertSee('Jumlah Barang');
        $response->assertSee('Harga HPP');
        $response->assertSee('SKU');
        $response->assertSee('Aksi');
        $response->assertSee('Print SKU');
        $response->assertSee('Edit');
        $response->assertSee('Mutasi Stok');
        $response->assertSee(route('stin.items.print-sku', $item));
        $response->assertSee(route('stin.items.edit', $item));
        $response->assertSee(route('stin.items.mutations', $item));

        // Check data item values
        $response->assertSee('Parfum Melati Mewah');
        $response->assertSee('Parfum Eksklusif');
        $response->assertSee('150 botol');
        $response->assertSee('Rp 125.000');
        $response->assertSee('SKU-MELATI-001');
    }

    public function test_sales_role_cannot_access_data_gudang_table(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $response = $this->actingAs($user)->get(route('stin.items.index'));

        $response->assertForbidden();
    }

    public function test_input_barang_button_is_visible_on_data_gudang(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $response = $this->actingAs($user)->get(route('stin.items.index'));

        $response->assertOk();
        $response->assertSee('Input Barang');
        $response->assertSee(route('stin.items.create'));
    }

    public function test_stin_user_can_view_input_barang_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $response = $this->actingAs($user)->get(route('stin.items.create'));

        $response->assertOk();
        $response->assertSee('Input Data Barang');
        $response->assertSee('Tambah ke Barang yang Sudah Ada');
        $response->assertSee('Buat Barang Baru');
        $response->assertSee('Detail Waktu');
        $response->assertSee('Waktu Proses');
        $response->assertSee('PIC');
        $response->assertSee('Tanda Tangan Digital');
        $response->assertSee('Kategori Barang');
    }

    public function test_stin_user_can_add_stock_to_existing_item(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR1',
            'name' => 'Cabang Utama',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Mawar',
            'category' => 'Parfum',
            'current_stock' => 10,
            'unit' => 'botol',
            'unit_price' => 50000,
            'sku' => 'SKU-MAWAR-001',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('stin.items.store'), [
            'entry_type' => 'existing',
            'warehouse_item_id' => $item->id,
            'quantity_add' => 25,
            'notes' => 'Restock kiriman pabrik',
            'pic_name' => 'Budi Santoso',
        ]);

        $response->assertRedirect(route('stin.items.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(35, $item->fresh()->current_stock);
        $this->assertDatabaseHas('warehouse_stock_movements', [
            'warehouse_item_id' => $item->id,
            'type' => 'in',
            'quantity' => 25,
            'stock_before' => 10,
            'stock_after' => 35,
            'pic_name' => 'Budi Santoso',
            'notes' => 'Restock kiriman pabrik',
        ]);
    }

    public function test_stin_user_can_create_new_item_with_initial_stock(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($user)->post(route('stin.items.store'), [
            'entry_type' => 'new',
            'name' => 'Parfum Vanilla Latte',
            'category' => 'Gourmand',
            'initial_stock' => 40,
            'unit' => 'botol',
            'unit_price' => 85000,
            'sku' => 'SKU-VANILLA-001',
            'pic_name' => 'Siti Rahma',
            'signature_data' => $dummySignature,
        ]);

        $response->assertRedirect(route('stin.items.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('warehouse_items', [
            'name' => 'Parfum Vanilla Latte',
            'category' => 'Gourmand',
            'current_stock' => 40,
            'unit' => 'botol',
            'sku' => 'SKU-VANILLA-001',
            'pic_name' => 'Siti Rahma',
        ]);

        $createdItem = WarehouseItem::where('sku', 'SKU-VANILLA-001')->first();
        $this->assertNotNull($createdItem->signature_path);
    }

    public function test_stin_user_can_view_mutations_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_MUT',
            'name' => 'Gudang Mutasi',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Oud Royal',
            'category' => 'Premium Oud',
            'current_stock' => 50,
            'unit' => 'botol',
            'unit_price' => 350000,
            'sku' => 'SKU-OUD-001',
            'is_active' => true,
        ]);

        $item->stockMovements()->create([
            'warehouse_branch_id' => $branch->id,
            'type' => 'in',
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
            'pic_name' => 'Ahmad Teknisi',
            'notes' => 'Penerimaan stok awal dari supplier',
            'user_id' => $user->id,
            'movement_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('stin.items.mutations', $item));

        $response->assertOk();
        $response->assertSee('Mutasi Stok');
        $response->assertSee('Parfum Oud Royal');
        $response->assertSee('SKU-OUD-001');
        $response->assertSee('+50 botol');
        $response->assertSee('Ahmad Teknisi');
        $response->assertSee('Penerimaan stok awal dari supplier');
    }

    public function test_stin_user_can_view_and_update_item_via_edit_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_EDIT',
            'name' => 'Gudang Edit',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Citrus Fresh',
            'category' => 'Citrus',
            'current_stock' => 15,
            'unit' => 'pcs',
            'unit_price' => 75000,
            'sku' => 'SKU-CITRUS-001',
            'is_active' => true,
        ]);

        // View edit page
        $this->actingAs($user)->get(route('stin.items.edit', $item))
            ->assertOk()
            ->assertSee('Edit Data Barang')
            ->assertSee('Parfum Citrus Fresh');

        // Update item
        $response = $this->actingAs($user)->put(route('stin.items.update', $item), [
            'name' => 'Parfum Citrus Fresh Exclusive',
            'category' => 'Citrus Premium',
            'unit' => 'botol',
            'unit_price' => 95000,
            'sku' => 'SKU-CITRUS-001',
        ]);

        $response->assertRedirect(route('stin.items.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('warehouse_items', [
            'id' => $item->id,
            'name' => 'Parfum Citrus Fresh Exclusive',
            'category' => 'Citrus Premium',
            'unit' => 'botol',
            'unit_price' => 95000,
        ]);
    }

    public function test_sales_role_cannot_access_mutations_and_edit_pages(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_ACC',
            'name' => 'Gudang Akses',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Item Rahasia',
            'current_stock' => 1,
            'sku' => 'SKU-RAHASIA-001',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('stin.items.mutations', $item))->assertForbidden();
        $this->actingAs($user)->get(route('stin.items.edit', $item))->assertForbidden();
        $this->actingAs($user)->put(route('stin.items.update', $item), [])->assertForbidden();
    }

    public function test_sales_role_cannot_access_input_barang_pages(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->actingAs($user)->get(route('stin.items.create'))->assertForbidden();
        $this->actingAs($user)->post(route('stin.items.store'), [])->assertForbidden();
    }

    public function test_stin_user_can_view_print_sku_page_with_1d_and_2d_barcodes(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_PRINT',
            'name' => 'Gudang Print',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Amber Rose',
            'category' => 'Amber Series',
            'current_stock' => 25,
            'unit' => 'botol',
            'unit_price' => 195000,
            'sku' => 'SKU-AMBER-001',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('stin.items.print-sku', $item));

        $response->assertOk();
        $response->assertSee('Print Barcode SKU');
        $response->assertSee('Parfum Amber Rose');
        $response->assertSee('SKU-AMBER-001');
        $response->assertSee('Mode Barcode');
        $response->assertSee('1D & 2D', false);
        $response->assertSee('Cetak Barcode');
        // Assert barcode and qr SVG content is sent
        $response->assertViewHas('barcode1d');
        $response->assertViewHas('barcode2d');
    }

    public function test_stin_user_can_customize_print_sku_options(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_PRINT2',
            'name' => 'Gudang Print 2',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Bergamot Breeze',
            'category' => 'Fresh Series',
            'current_stock' => 10,
            'unit' => 'botol',
            'unit_price' => 120000,
            'sku' => 'SKU-BERGA-002',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('stin.items.print-sku', [
            'item' => $item,
            'layout' => '1d',
            'size' => 'compact',
            'copies' => 5,
        ]));

        $response->assertOk();
        $response->assertViewHas('layout', '1d');
        $response->assertViewHas('size', 'compact');
        $response->assertViewHas('copies', 5);
    }

    public function test_sales_role_cannot_access_print_sku_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_PRINT_DIS',
            'name' => 'Gudang Print Disallowed',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Barang Terbatas',
            'current_stock' => 5,
            'sku' => 'SKU-LIMITED-001',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('stin.items.print-sku', $item))->assertForbidden();
    }

    public function test_stin_user_can_search_items_by_name_sku_and_category(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_SEARCH',
            'name' => 'Gudang Search',
            'is_active' => true,
        ]);

        $itemA = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Kasturi Putih',
            'category' => 'Minyak Wangi',
            'current_stock' => 30,
            'unit' => 'botol',
            'unit_price' => 110000,
            'sku' => 'SKU-KASTURI-001',
            'is_active' => true,
        ]);

        $itemB = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Dupa Gaharu Premium',
            'category' => 'Aromaterapi',
            'current_stock' => 15,
            'unit' => 'box',
            'unit_price' => 250000,
            'sku' => 'SKU-GAHARU-002',
            'is_active' => true,
        ]);

        // 1. Search by name
        $responseName = $this->actingAs($user)->get(route('stin.items.index', ['search' => 'Kasturi']));
        $responseName->assertOk();
        $responseName->assertSee('Parfum Kasturi Putih');
        $responseName->assertDontSee('Dupa Gaharu Premium');

        // 2. Search by SKU
        $responseSku = $this->actingAs($user)->get(route('stin.items.index', ['search' => 'SKU-GAHARU']));
        $responseSku->assertOk();
        $responseSku->assertSee('Dupa Gaharu Premium');
        $responseSku->assertDontSee('Parfum Kasturi Putih');

        // 3. Search by category
        $responseCat = $this->actingAs($user)->get(route('stin.items.index', ['search' => 'Aromaterapi']));
        $responseCat->assertOk();
        $responseCat->assertSee('Dupa Gaharu Premium');
        $responseCat->assertDontSee('Parfum Kasturi Putih');

        // 4. Search with zero results
        $responseNone = $this->actingAs($user)->get(route('stin.items.index', ['search' => 'BarangYangTidakAdaX123']));
        $responseNone->assertOk();
        $responseNone->assertSee('Tidak ada data barang yang sesuai dengan pencarian');
        $responseNone->assertDontSee('Parfum Kasturi Putih');
        $responseNone->assertDontSee('Dupa Gaharu Premium');
    }

    public function test_stin_user_can_access_scanner_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $response = $this->actingAs($user)->get(route('stin.scanner.index'));

        $response->assertOk();
        $response->assertSee('Sistem Scan Barcode');
        $response->assertSee('Cek Stok (Lookup)');
        $response->assertSee('Barang Masuk (+)');
        $response->assertSee('Barang Keluar (-)');
        $response->assertSee('barcodeInput');
    }

    public function test_stin_user_can_lookup_item_via_scanner_api(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_SCAN',
            'name' => 'Gudang Scan',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Lavender Dreams',
            'category' => 'Floral',
            'current_stock' => 50,
            'unit' => 'botol',
            'unit_price' => 125000,
            'sku' => 'SKU-LAVENDER-001',
            'barcode' => '8991234567890',
            'is_active' => true,
        ]);

        // Lookup by SKU
        $resBySku = $this->actingAs($user)->postJson(route('stin.scanner.lookup'), [
            'code' => 'SKU-LAVENDER-001',
        ]);
        $resBySku->assertOk();
        $resBySku->assertJson([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => 'Parfum Lavender Dreams',
                'sku' => 'SKU-LAVENDER-001',
                'current_stock' => 50,
            ],
        ]);

        // Lookup by Barcode
        $resByBarcode = $this->actingAs($user)->postJson(route('stin.scanner.lookup'), [
            'code' => '8991234567890',
        ]);
        $resByBarcode->assertOk();
        $resByBarcode->assertJson([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => 'Parfum Lavender Dreams',
            ],
        ]);

        // Lookup non-existent item
        $resNotFound = $this->actingAs($user)->postJson(route('stin.scanner.lookup'), [
            'code' => 'KODE-TIDAK-ADA-999',
        ]);
        $resNotFound->assertNotFound();
        $resNotFound->assertJson([
            'success' => false,
        ]);
    }

    public function test_stin_user_can_perform_quick_stock_in_and_out_via_scanner(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Petugas Scanner',
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_SCAN_ACT',
            'name' => 'Gudang Action',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Minyak Gaharu Asli',
            'category' => 'Oud',
            'current_stock' => 20,
            'unit' => 'botol',
            'unit_price' => 500000,
            'sku' => 'SKU-GAHARU-ASLI',
            'is_active' => true,
        ]);

        // 1. Stock In via scanner
        $resIn = $this->actingAs($user)->postJson(route('stin.scanner.action'), [
            'warehouse_item_id' => $item->id,
            'action_type' => 'in',
            'quantity' => 10,
            'notes' => 'Penerimaan batch barcode 1',
            'pic_name' => 'Budi Petugas Scanner',
        ]);

        $resIn->assertOk();
        $resIn->assertJson([
            'success' => true,
            'item' => [
                'current_stock' => 30,
            ],
        ]);
        $this->assertEquals(30, $item->fresh()->current_stock);

        // 2. Stock Out via scanner
        $resOut = $this->actingAs($user)->postJson(route('stin.scanner.action'), [
            'warehouse_item_id' => $item->id,
            'action_type' => 'out',
            'quantity' => 5,
            'notes' => 'Pengeluaran display',
            'pic_name' => 'Budi Petugas Scanner',
        ]);

        $resOut->assertOk();
        $resOut->assertJson([
            'success' => true,
            'item' => [
                'current_stock' => 25,
            ],
        ]);
        $this->assertEquals(25, $item->fresh()->current_stock);

        // 3. Stock Out without pic_name (null default)
        $resNoPic = $this->actingAs($user)->postJson(route('stin.scanner.action'), [
            'warehouse_item_id' => $item->id,
            'action_type' => 'out',
            'quantity' => 2,
        ]);
        $resNoPic->assertOk();
        $resNoPic->assertJson([
            'success' => true,
            'item' => [
                'current_stock' => 23,
            ],
            'log' => [
                'pic_name' => '-',
            ],
        ]);
        $this->assertEquals(23, $item->fresh()->current_stock);

        // 4. Stock Out exceeds available stock
        $resExcess = $this->actingAs($user)->postJson(route('stin.scanner.action'), [
            'warehouse_item_id' => $item->id,
            'action_type' => 'out',
            'quantity' => 999,
        ]);

        $resExcess->assertStatus(422);
        $resExcess->assertJson([
            'success' => false,
        ]);
        $this->assertEquals(23, $item->fresh()->current_stock);
    }

    public function test_stin_user_can_perform_batch_stock_out_via_scanner(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
            'name' => 'Operator Gudang STIN',
        ]);

        $branch = WarehouseBranch::create([
            'code' => 'BR_BATCH_WH',
            'name' => 'Gudang Batch STIN',
            'is_active' => true,
        ]);

        $itemA = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'sku' => 'SKU-BATCH-A',
            'barcode' => 'BAR-BATCH-A',
            'name' => 'Bahan Baku Keramik A',
            'unit' => 'pcs',
            'current_stock' => 50,
            'min_stock' => 5,
            'unit_price' => 20000,
            'is_active' => true,
        ]);

        $itemB = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'sku' => 'SKU-BATCH-B',
            'barcode' => 'BAR-BATCH-B',
            'name' => 'Bahan Baku Keramik B',
            'unit' => 'dus',
            'current_stock' => 100,
            'min_stock' => 10,
            'unit_price' => 50000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('stin.scanner.batch-action'), [
            'action_type' => 'out',
            'items' => [
                ['warehouse_item_id' => $itemA->id, 'quantity' => 15],
                ['warehouse_item_id' => $itemB->id, 'quantity' => 30],
            ],
            'notes' => 'Pengeluaran Batch Surat Jalan #SJ-2026-001',
            'pic_name' => 'Pak Budi',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertEquals(35, $itemA->fresh()->current_stock);
        $this->assertEquals(70, $itemB->fresh()->current_stock);
    }

    public function test_stin_user_can_perform_batch_stock_out_with_digital_signature(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::firstOrCreate(['code' => 'STIN'], [
            'name' => 'Gudang STIN',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'sku' => 'MAT-SIG-001',
            'name' => 'Produk Dengan Tanda Tangan',
            'unit' => 'pcs',
            'current_stock' => 50,
            'min_stock' => 5,
            'unit_price' => 10000,
            'is_active' => true,
        ]);

        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $response = $this->actingAs($user)->postJson(route('stin.scanner.batch-action'), [
            'action_type' => 'out',
            'items' => [
                ['warehouse_item_id' => $item->id, 'quantity' => 10],
            ],
            'notes' => 'Pengeluaran Batch dengan Tanda Tangan',
            'pic_name' => 'Petugas Test',
            'signature_data' => $fakeSignature,
        ]);

        $response->assertOk();
        $this->assertEquals(40, $item->fresh()->current_stock);

        $movement = WarehouseStockMovement::where('warehouse_item_id', $item->id)->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertNotNull($movement->signature_path);
        Storage::disk('public')->assertExists($movement->signature_path);
    }

    public function test_sales_role_cannot_access_scanner_system(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->actingAs($user)->get(route('stin.scanner.index'))->assertForbidden();
        $this->actingAs($user)->postJson(route('stin.scanner.lookup'), ['code' => 'SKU-TEST'])->assertForbidden();
        $this->actingAs($user)->postJson(route('stin.scanner.action'), [])->assertForbidden();
        $this->actingAs($user)->postJson(route('stin.scanner.batch-action'), [])->assertForbidden();
    }

    public function test_stin_user_can_export_item_mutations_to_excel(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::firstOrCreate(['code' => 'STIN'], [
            'name' => 'Gudang STIN',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Melati Eksklusif',
            'category' => 'Floral Series',
            'sku' => 'SKU-EXP-001',
            'current_stock' => 50,
            'unit' => 'botol',
            'unit_price' => 120000,
            'is_active' => true,
        ]);

        $item->addStock(20, 'Stok Awal Melati', 'PIC Tester');
        $item->removeStock(5, 'Pengambilan Tester Toko', 'PIC Toko');

        $response = $this->actingAs($user)->get(route('stin.items.mutations.export', $item));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('mutasi_stok_sku-exp-001_', $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Parfum Melati Eksklusif', $content);
        $this->assertStringContainsString('SKU-EXP-001', $content);
        $this->assertStringContainsString('Stok Masuk', $content);
        $this->assertStringContainsString('Stok Keluar', $content);
    }

    public function test_stin_user_can_export_all_warehouse_mutations_to_excel(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STIN,
        ]);

        $branch = WarehouseBranch::firstOrCreate(['code' => 'STIN'], [
            'name' => 'Gudang STIN',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Cendana Premium',
            'category' => 'Woody Series',
            'sku' => 'SKU-EXP-002',
            'current_stock' => 10,
            'unit' => 'botol',
            'unit_price' => 180000,
            'is_active' => true,
        ]);

        $item->addStock(10, 'Restock Batch A', 'Staff Restock');

        $response = $this->actingAs($user)->get(route('stin.mutations.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('mutasi_stok_gudang_', $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Parfum Cendana Premium', $content);
        $this->assertStringContainsString('SKU-EXP-002', $content);
    }

    public function test_sales_role_cannot_export_warehouse_mutations(): void
    {
        $salesUser = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $branch = WarehouseBranch::firstOrCreate(['code' => 'STIN'], [
            'name' => 'Gudang STIN',
            'is_active' => true,
        ]);

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Item Rahasia',
            'current_stock' => 1,
            'sku' => 'SKU-SECRET',
            'is_active' => true,
        ]);

        $this->actingAs($salesUser)->get(route('stin.items.mutations.export', $item))->assertForbidden();
        $this->actingAs($salesUser)->get(route('stin.mutations.export'))->assertForbidden();
    }
}
