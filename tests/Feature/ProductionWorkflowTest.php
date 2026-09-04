<?php

namespace Tests\Feature;

use App\Enums\ProductionRequestStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\ProductionRequest;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_user_can_create_batch_and_decrements_materials_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $product = Product::create([
            'sku' => 'GHR-TEST-001',
            'name' => 'Gaharu Sana\'i Super Malaki',
            'price' => 4500000.00,
            'stock_quantity' => 0,
            'unit' => 'kg',
        ]);

        $matKayu = Material::create([
            'code' => 'MAT-KY-01',
            'name' => 'Kayu Medang Kering',
            'category' => 'Kayu Dasar',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
        ]);

        $matMinyak = Material::create([
            'code' => 'MAT-MY-01',
            'name' => 'Minyak Gaharu Murni',
            'category' => 'Minyak & Resin',
            'unit' => 'tola',
            'stock_quantity' => 20.00,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.batches.store'), [
            'product_id' => $product->id,
            'target_quantity' => 10.00,
            'start_date' => '2026-09-04',
            'target_completion_date' => '2026-09-07',
            'pic_name' => 'Mandor Pabrik Sentul',
            'notes' => 'Formula infusi pekat 4 bar.',
            'materials' => [
                ['material_id' => $matKayu->id, 'quantity_used' => 10.00],
                ['material_id' => $matMinyak->id, 'quantity_used' => 2.00],
            ],
        ]);

        $response->assertSessionHas('success');
        $batch = ProductionBatch::latest('id')->first();
        $this->assertNotNull($batch);
        $this->assertEquals('in_progress', $batch->status);
        $this->assertEquals(10.00, (float) $batch->target_quantity);

        // Verify materials stock decremented: Kayu 50 - 10 = 40, Minyak 20 - 2 = 18
        $matKayu->refresh();
        $matMinyak->refresh();
        $this->assertEquals(40.00, (float) $matKayu->stock_quantity);
        $this->assertEquals(18.00, (float) $matMinyak->stock_quantity);

        // Verify initial daily log created
        $this->assertDatabaseHas('production_daily_logs', [
            'production_batch_id' => $batch->id,
            'pic_name' => 'Mandor Pabrik Sentul',
        ]);
    }

    public function test_batch_creation_fails_if_material_stock_is_insufficient(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $product = Product::create([
            'sku' => 'GHR-TEST-002',
            'name' => 'Gaharu Sana\'i Maroke',
            'price' => 2800000.00,
            'stock_quantity' => 0,
            'unit' => 'kg',
        ]);

        $material = Material::create([
            'code' => 'MAT-LOW-01',
            'name' => 'Resin Langka',
            'category' => 'Minyak & Resin',
            'unit' => 'kg',
            'stock_quantity' => 2.00, // hanya 2 kg
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.batches.store'), [
            'product_id' => $product->id,
            'target_quantity' => 10.00,
            'start_date' => '2026-09-04',
            'pic_name' => 'Mandor Pabrik',
            'materials' => [
                ['material_id' => $material->id, 'quantity_used' => 5.00], // butuh 5 kg (lebih besar dari stok)
            ],
        ]);

        $response->assertSessionHasErrors('materials');
        $this->assertEquals(0, ProductionBatch::count());
        $material->refresh();
        $this->assertEquals(2.00, (float) $material->stock_quantity);
    }

    public function test_production_user_can_add_daily_production_log(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $product = Product::create([
            'sku' => 'GHR-TEST-003',
            'name' => 'Gaharu Sana\'i Malaki',
            'price' => 4500000.00,
            'stock_quantity' => 0,
        ]);

        $batch = ProductionBatch::create([
            'batch_number' => 'BATCH-TEST-001',
            'product_id' => $product->id,
            'target_quantity' => 10.00,
            'unit' => 'kg',
            'status' => 'in_progress',
            'stage' => '1. Persiapan Bahan & Sortir Kayu',
            'start_date' => '2026-09-04',
            'pic_name' => 'Mandor Pabrik',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.batches.store-log', $batch), [
            'log_date' => '2026-09-05',
            'stage' => '2. Infusi / Vacuum Pressure Resin & Minyak',
            'progress_percentage' => 50,
            'pic_name' => 'Asep Kurniawan',
            'notes' => 'Proses infusi minyak gaharu berjalan lancar pada tekanan 4 bar.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('production_daily_logs', [
            'production_batch_id' => $batch->id,
            'stage' => '2. Infusi / Vacuum Pressure Resin & Minyak',
            'progress_percentage' => 50,
        ]);

        $batch->refresh();
        $this->assertEquals('2. Infusi / Vacuum Pressure Resin & Minyak', $batch->stage);
    }

    public function test_completing_production_batch_increments_finished_product_stock_and_completes_sales_request(): void
    {
        $user = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $customer = Customer::create(['name' => 'Pembeli Gaharu']);
        $product = Product::create([
            'sku' => 'GHR-TEST-004',
            'name' => 'Gaharu Sana\'i Bukhoor Press',
            'price' => 650000.00,
            'stock_quantity' => 20,
            'unit' => 'kotak',
        ]);

        $so = SalesOrder::create([
            'customer_id' => $customer->id,
            'order_number' => 'SO-TEST-001',
            'total_amount' => 10000000,
        ]);

        $pr = ProductionRequest::create([
            'sales_order_id' => $so->id,
            'request_number' => 'PR-TEST-001',
            'status' => ProductionRequestStatus::IN_PRODUCTION,
            'requested_date' => now(),
        ]);

        $batch = ProductionBatch::create([
            'batch_number' => 'BATCH-TEST-002',
            'product_id' => $product->id,
            'production_request_id' => $pr->id,
            'target_quantity' => 30.00,
            'unit' => 'kotak',
            'status' => 'in_progress',
            'stage' => '4. Quality Control & Pengujian Bakar',
            'start_date' => '2026-09-01',
            'pic_name' => 'Mandor Pabrik',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('production.batches.complete', $batch), [
            'actual_quantity' => 30.00,
            'completed_date' => '2026-09-04',
            'branch_code' => 'PLANT-STL',
            'notes' => 'Lolos uji mutu bakar dan kemasan rapi.',
        ]);

        $response->assertRedirect(route('production.batches.show', $batch));
        $response->assertSessionHas('success');

        $batch->refresh();
        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(30.00, (float) $batch->actual_quantity);

        // Verify product stock incremented: 20 + 30 = 50
        $product->refresh();
        $this->assertEquals(50, $product->stock_quantity);

        // Verify product branch created or incremented
        $this->assertDatabaseHas('product_branches', [
            'product_id' => $product->id,
            'branch_code' => 'PLANT-STL',
            'quantity' => 30,
        ]);

        // Verify linked sales production request marked finished
        $pr->refresh();
        $this->assertEquals(ProductionRequestStatus::FINISHED, $pr->status);
    }
}
