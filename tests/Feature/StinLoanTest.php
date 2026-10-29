<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WarehouseLoanStatus;
use App\Models\User;
use App\Models\WarehouseBranch;
use App\Models\WarehouseItem;
use App\Models\WarehouseLoan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StinLoanTest extends TestCase
{
    use RefreshDatabase;

    private function createStinUser(): User
    {
        return User::factory()->create([
            'role' => UserRole::STIN,
            'name' => 'Petugas STIN Test',
        ]);
    }

    private function createSampleItem(int $stock = 20, string $sku = 'SKU-SAMPLE-01'): WarehouseItem
    {
        $branch = WarehouseBranch::firstOrCreate(['code' => 'STIN'], [
            'name' => 'Gudang STIN',
            'is_active' => true,
        ]);

        return WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => 'Parfum Mawar Sample',
            'category' => 'Display Series',
            'sku' => $sku,
            'barcode' => $sku,
            'current_stock' => $stock,
            'unit' => 'botol',
            'unit_price' => 150000,
            'is_active' => true,
        ]);
    }

    public function test_stin_user_can_access_loans_index_page(): void
    {
        $user = $this->createStinUser();
        $response = $this->actingAs($user)->get(route('stin.loans.index'));

        $response->assertOk();
        $response->assertSee('Barang Dipinjam');
        $response->assertSee('+ Pinjam Barang');
        // Pastikan tidak ada kartu KPI summary, kata pajangan, dan tab sebagian kembali
        $response->assertDontSee('Total Peminjaman');
        $response->assertDontSee('Pajangan');
        $response->assertDontSee('Sebagian Kembali');
        $response->assertSee('Lewat Batas (Overdue)');
    }

    public function test_sales_role_cannot_access_loans_pages(): void
    {
        $salesUser = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->actingAs($salesUser)->get(route('stin.loans.index'))->assertForbidden();
        $this->actingAs($salesUser)->get(route('stin.loans.create'))->assertForbidden();
        $this->actingAs($salesUser)->post(route('stin.loans.store'), [])->assertForbidden();
    }

    public function test_stin_user_can_view_create_loan_page(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(15);

        $response = $this->actingAs($user)->get(route('stin.loans.create'));

        $response->assertOk();
        $response->assertSee('Form Peminjaman Barang');
        $response->assertSee($item->name);
    }

    public function test_stin_user_can_create_new_loan_and_decrements_stock(): void
    {
        Storage::fake('public');
        $user = $this->createStinUser();
        $itemA = $this->createSampleItem(20, 'SKU-LOAN-A');
        $itemB = $this->createSampleItem(15, 'SKU-LOAN-B');

        $fakeSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $payload = [
            'borrower_name' => 'Ahmad Syarif',
            'borrower_contact' => '081299887766',
            'purpose' => 'Pajangan Etalase Toko Utama',
            'loan_date' => now()->format('Y-m-d'),
            'expected_return_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Pajangan display etalase depan',
            'signature_data' => $fakeSig,
            'items' => [
                [
                    'warehouse_item_id' => $itemA->id,
                    'quantity' => 5,
                    'notes' => 'Botol pajangan A',
                ],
                [
                    'warehouse_item_id' => $itemB->id,
                    'quantity' => 3,
                    'notes' => 'Botol pajangan B',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('stin.loans.store'), $payload);

        $loan = WarehouseLoan::where('borrower_name', 'Ahmad Syarif')->first();
        $this->assertNotNull($loan);
        $response->assertRedirect(route('stin.loans.show', $loan));
        $response->assertSessionHas('success');

        // Verify status
        $this->assertEquals(WarehouseLoanStatus::BORROWED, $loan->status);
        $this->assertEquals('Ahmad Syarif', $loan->borrower_name);
        $this->assertEquals(8, $loan->totalQuantityLoaned());
        $this->assertEquals(0, $loan->totalQuantityReturned());
        $this->assertEquals(8, $loan->totalQuantityRemaining());
        $this->assertNotNull($loan->signature_path);
        Storage::disk('public')->assertExists($loan->signature_path);

        // Verify stock decremented
        $this->assertEquals(15, $itemA->fresh()->current_stock);
        $this->assertEquals(12, $itemB->fresh()->current_stock);

        // Verify stock movement logged as 'out'
        $this->assertDatabaseHas('warehouse_stock_movements', [
            'warehouse_item_id' => $itemA->id,
            'type' => 'out',
            'quantity' => 5,
            'stock_after' => 15,
            'pic_name' => 'Ahmad Syarif',
        ]);
    }

    public function test_cannot_create_loan_when_requested_quantity_exceeds_stock(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(5, 'SKU-LOW');

        $response = $this->actingAs($user)->post(route('stin.loans.store'), [
            'borrower_name' => 'Budi Display',
            'purpose' => 'Pameran Mall',
            'loan_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'warehouse_item_id' => $item->id,
                    'quantity' => 10, // Exceeds available stock (5)
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertEquals(5, $item->fresh()->current_stock);
        $this->assertEquals(0, WarehouseLoan::count());
    }

    public function test_stin_user_can_view_loan_detail_page(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(10);

        $loan = WarehouseLoan::create([
            'loan_number' => WarehouseLoan::generateLoanNumber(),
            'borrower_name' => 'Rina Marketing',
            'purpose' => 'Tester Event',
            'loan_date' => now()->subDay(),
            'expected_return_date' => now()->addDays(2),
            'status' => WarehouseLoanStatus::BORROWED,
            'user_id' => $user->id,
        ]);

        $loan->items()->create([
            'warehouse_item_id' => $item->id,
            'quantity_loaned' => 4,
            'quantity_returned' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('stin.loans.show', $loan));

        $response->assertOk();
        $response->assertSee($loan->loan_number);
        $response->assertSee('Rina Marketing');
        $response->assertSee('Tester Event');
        $response->assertSee($item->name);
        $response->assertSee('4 botol');
    }

    public function test_stin_user_can_process_partial_return_and_increments_stock(): void
    {
        Storage::fake('public');
        $user = $this->createStinUser();
        $item = $this->createSampleItem(10, 'SKU-RETURN-PARTIAL');

        $loan = WarehouseLoan::create([
            'loan_number' => WarehouseLoan::generateLoanNumber(),
            'borrower_name' => 'Hendra Outlet',
            'purpose' => 'Display Etalase',
            'loan_date' => now()->subDays(3),
            'expected_return_date' => now()->addDays(4),
            'status' => WarehouseLoanStatus::BORROWED,
            'user_id' => $user->id,
        ]);

        $loanItem = $loan->items()->create([
            'warehouse_item_id' => $item->id,
            'quantity_loaned' => 6,
            'quantity_returned' => 0,
        ]);

        // Stock was 10, simulate that it had been removed to 4
        $item->update(['current_stock' => 4]);

        $fakeSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        // Return partially: 4 units returned out of 6
        $response = $this->actingAs($user)->post(route('stin.loans.return.process', $loan), [
            'receiver_name' => 'Siti Gudang Penerima',
            'return_date' => now()->format('Y-m-d'),
            'notes' => 'Barang kembali 4 botol dalam kondisi baik',
            'signature_data' => $fakeSig,
            'returns' => [
                [
                    'warehouse_loan_item_id' => $loanItem->id,
                    'quantity' => 4,
                    'condition' => 'good',
                    'notes' => 'Kemasan utuh',
                ],
            ],
        ]);

        $response->assertRedirect(route('stin.loans.show', $loan));
        $response->assertSessionHas('success');

        $loan->refresh();
        $loanItem->refresh();

        // Verify loan status is now PARTIAL
        $this->assertEquals(WarehouseLoanStatus::PARTIAL, $loan->status);
        $this->assertEquals(4, $loanItem->quantity_returned);
        $this->assertEquals(2, $loanItem->remainingQuantity());
        $this->assertEquals(2, $loan->totalQuantityRemaining());

        // Stock in warehouse incremented from 4 to 8
        $this->assertEquals(8, $item->fresh()->current_stock);

        // Verify return record created
        $this->assertDatabaseHas('warehouse_loan_returns', [
            'warehouse_loan_id' => $loan->id,
            'receiver_name' => 'Siti Gudang Penerima',
        ]);

        $this->assertDatabaseHas('warehouse_loan_return_items', [
            'warehouse_loan_item_id' => $loanItem->id,
            'warehouse_item_id' => $item->id,
            'quantity_returned' => 4,
            'condition' => 'good',
        ]);

        // Verify stock movement logged as 'in'
        $this->assertDatabaseHas('warehouse_stock_movements', [
            'warehouse_item_id' => $item->id,
            'type' => 'in',
            'quantity' => 4,
            'stock_after' => 8,
            'pic_name' => 'Siti Gudang Penerima',
        ]);
    }

    public function test_stin_user_can_process_full_return_and_loan_status_becomes_returned(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(2, 'SKU-FULL-RETURN');

        $loan = WarehouseLoan::create([
            'loan_number' => WarehouseLoan::generateLoanNumber(),
            'borrower_name' => 'Dedi Pajangan',
            'purpose' => 'Pameran Pusat',
            'loan_date' => now()->subDay(),
            'status' => WarehouseLoanStatus::BORROWED,
            'user_id' => $user->id,
        ]);

        $loanItem = $loan->items()->create([
            'warehouse_item_id' => $item->id,
            'quantity_loaned' => 5,
            'quantity_returned' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('stin.loans.return.process', $loan), [
            'receiver_name' => 'Budi Staff STIN',
            'return_date' => now()->format('Y-m-d'),
            'returns' => [
                [
                    'warehouse_loan_item_id' => $loanItem->id,
                    'quantity' => 5,
                    'condition' => 'good',
                ],
            ],
        ]);

        $response->assertRedirect(route('stin.loans.show', $loan));
        $loan->refresh();

        // Status must be RETURNED
        $this->assertEquals(WarehouseLoanStatus::RETURNED, $loan->status);
        $this->assertNotNull($loan->returned_at);
        $this->assertEquals(0, $loan->totalQuantityRemaining());
        $this->assertEquals(7, $item->fresh()->current_stock); // 2 + 5 = 7
    }

    public function test_cannot_return_more_than_remaining_loan_quantity(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(5);

        $loan = WarehouseLoan::create([
            'loan_number' => WarehouseLoan::generateLoanNumber(),
            'borrower_name' => 'Peminjam A',
            'purpose' => 'Test',
            'loan_date' => now(),
            'status' => WarehouseLoanStatus::BORROWED,
            'user_id' => $user->id,
        ]);

        $loanItem = $loan->items()->create([
            'warehouse_item_id' => $item->id,
            'quantity_loaned' => 3,
            'quantity_returned' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('stin.loans.return.process', $loan), [
            'receiver_name' => 'Petugas',
            'return_date' => now()->format('Y-m-d'),
            'returns' => [
                [
                    'warehouse_loan_item_id' => $loanItem->id,
                    'quantity' => 10, // exceeds 3
                ],
            ],
        ]);

        $response->assertSessionHasErrors('returns');
        $this->assertEquals(0, $loanItem->fresh()->quantity_returned);
    }

    public function test_cannot_access_return_form_when_loan_already_fully_returned(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(5);

        $loan = WarehouseLoan::create([
            'loan_number' => WarehouseLoan::generateLoanNumber(),
            'borrower_name' => 'Lunas Test',
            'purpose' => 'Display Selesai',
            'loan_date' => now()->subDays(5),
            'status' => WarehouseLoanStatus::RETURNED,
            'returned_at' => now(),
            'user_id' => $user->id,
        ]);

        $loan->items()->create([
            'warehouse_item_id' => $item->id,
            'quantity_loaned' => 2,
            'quantity_returned' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('stin.loans.return', $loan));
        $response->assertRedirect(route('stin.loans.show', $loan));
        $response->assertSessionHas('info');
    }

    public function test_scanner_lookup_displays_currently_loaned_quantity(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(10, 'SKU-LOOKUP-LOAN');

        $loan = WarehouseLoan::create([
            'loan_number' => WarehouseLoan::generateLoanNumber(),
            'borrower_name' => 'Peminjam Showcase',
            'purpose' => 'Display Showcase',
            'loan_date' => now(),
            'status' => WarehouseLoanStatus::BORROWED,
            'user_id' => $user->id,
        ]);

        $loan->items()->create([
            'warehouse_item_id' => $item->id,
            'quantity_loaned' => 4,
            'quantity_returned' => 1, // 3 remaining
        ]);

        $response = $this->actingAs($user)->postJson(route('stin.scanner.lookup'), [
            'code' => 'SKU-LOOKUP-LOAN',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'currently_loaned' => 3,
            ],
        ]);
    }

    public function test_dashboard_displays_loan_metrics_and_recent_loans(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(10);

        $loan = WarehouseLoan::create([
            'loan_number' => 'PINJAM-DASH-001',
            'borrower_name' => 'Sari Display Mall',
            'purpose' => 'Pajangan Mall',
            'loan_date' => now(),
            'status' => WarehouseLoanStatus::BORROWED,
            'user_id' => $user->id,
        ]);

        $loan->items()->create([
            'warehouse_item_id' => $item->id,
            'quantity_loaned' => 5,
            'quantity_returned' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('stin.dashboard'));

        $response->assertOk();
        $response->assertSee('Barang Dipinjam');
        $response->assertSee('PINJAM-DASH-001');
        $response->assertSee('Sari Display Mall');
    }

    public function test_scanner_page_contains_loan_mode_and_inputs(): void
    {
        $user = $this->createStinUser();

        $response = $this->actingAs($user)->get(route('stin.scanner.index'));

        $response->assertOk();
        $response->assertSee('Pinjam Barang (-)');
        $response->assertSee('modalLoanFieldsContainer', false);
        $response->assertSee('modalBorrowerName', false);
        $response->assertSee('modalLoanPurpose', false);
        $response->assertDontSee('modalBorrowerContact', false);
    }

    public function test_stin_user_can_create_loan_via_scanner_json_endpoint(): void
    {
        $user = $this->createStinUser();
        $item = $this->createSampleItem(25, 'SKU-SCAN-LOAN');

        $payload = [
            'borrower_name' => 'Dimas Lapangan',
            'borrower_contact' => '089911223344',
            'purpose' => 'Peminjaman Kegiatan Pameran',
            'loan_date' => now()->format('Y-m-d'),
            'expected_return_date' => now()->addDays(5)->format('Y-m-d'),
            'notes' => 'Peminjaman via barcode scanner',
            'items' => [
                [
                    'warehouse_item_id' => $item->id,
                    'quantity' => 7,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->postJson(route('stin.loans.store'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'loan' => [
                'borrower_name' => 'Dimas Lapangan',
                'purpose' => 'Peminjaman Kegiatan Pameran',
                'total_quantity' => 7,
            ],
            'log' => [
                'action_type' => 'loan',
                'quantity' => 7,
            ],
        ]);

        $this->assertEquals(18, $item->fresh()->current_stock);
        $this->assertDatabaseHas('warehouse_loans', [
            'borrower_name' => 'Dimas Lapangan',
            'purpose' => 'Peminjaman Kegiatan Pameran',
        ]);
        $this->assertDatabaseHas('warehouse_stock_movements', [
            'warehouse_item_id' => $item->id,
            'type' => 'out',
            'quantity' => 7,
            'stock_after' => 18,
            'pic_name' => 'Dimas Lapangan',
        ]);
    }
}
