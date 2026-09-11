<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_role_can_access_sales_dashboard_but_forbidden_from_production(): void
    {
        $salesUser = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->actingAs($salesUser);

        // Can access sales dashboard
        $this->get('/')->assertStatus(200);

        // Cannot access production dashboard
        $this->get('/production')->assertStatus(403);

        // Cannot access STIN dashboard
        $this->get('/stin')->assertStatus(403);
    }

    public function test_production_role_can_access_production_dashboard_but_forbidden_from_sales(): void
    {
        $prodUser = User::factory()->create([
            'role' => UserRole::PRODUCTION,
        ]);

        $this->actingAs($prodUser);

        // Can access production dashboard
        $this->get('/production')->assertStatus(200);

        // Root route smoothly redirects to production dashboard
        $this->get('/')->assertRedirect(route('production.dashboard'));

        // Cannot access sales modules
        $this->get('/customers')->assertStatus(403);
        $this->get('/sales-orders')->assertStatus(403);
        $this->get('/invoices')->assertStatus(403);
    }

    public function test_super_role_can_access_all_modules(): void
    {
        $superUser = User::factory()->create([
            'role' => UserRole::SUPER_ROLE,
        ]);

        $this->actingAs($superUser);

        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertSee('EK.DIV SALES');
        $res->assertSee('Sales Orders');
        $res->assertDontSee('EK.DIV SUPER ROLE');

        $this->get('/production')->assertStatus(200);
        $this->get('/stin')->assertStatus(200);
        $this->get('/crm')->assertStatus(200);
        $this->get('/ecommerce')->assertStatus(200);
    }

    public function test_stin_crm_ecommerce_modules_display_under_development_notice(): void
    {
        $superUser = User::factory()->create([
            'role' => UserRole::SUPER_ROLE,
        ]);

        $this->actingAs($superUser);

        $this->get('/stin')
            ->assertStatus(200)
            ->assertSee('Tahap Pengembangan')
            ->assertSee('Divisi Khusus STIN');

        $this->get('/crm')
            ->assertStatus(200)
            ->assertSee('Tahap Pengembangan')
            ->assertSee('Manajemen Hubungan Pelanggan (CRM)');

        $this->get('/ecommerce')
            ->assertStatus(200)
            ->assertSee('Tahap Pengembangan')
            ->assertSee('Manajemen Kanal Digital & E-Commerce');
    }

    public function test_sales_role_can_view_products_but_cannot_create_or_update_products(): void
    {
        $salesUser = User::factory()->create(['role' => UserRole::SALES]);
        $prod = Product::create([
            'sku' => 'PRD-TEST-001',
            'name' => 'Produk Tes',
            'price' => 50000,
            'unit' => 'kg',
            'stock_quantity' => 10,
        ]);

        $this->actingAs($salesUser);

        // Can view catalog
        $response = $this->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('Mode Katalog (Lihat Saja)');
        $response->assertDontSee('+ Tambah Jenis Produk');
        $response->assertDontSee('Edit Produk');

        // Cannot create product
        $this->post(route('products.store'), [
            'name' => 'New Forbidden Product',
            'price' => 10000,
        ])->assertStatus(403);

        // Cannot update product
        $this->put(route('products.update', $prod), [
            'sku' => 'PRD-TEST-001',
            'name' => 'Updated Name',
            'price' => 60000,
            'unit' => 'kg',
            'stock_quantity' => 20,
        ])->assertStatus(403);
    }

    public function test_production_role_can_create_and_update_products(): void
    {
        $prodUser = User::factory()->create(['role' => UserRole::PRODUCTION]);
        $prod = Product::create([
            'sku' => 'PRD-TEST-002',
            'name' => 'Produk Produksi',
            'price' => 75000,
            'unit' => 'kg',
            'stock_quantity' => 15,
        ]);

        $this->actingAs($prodUser);

        $response = $this->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('+ Tambah Jenis Produk');
        $response->assertSee('Edit Produk');

        // Can update product
        $this->put(route('products.update', $prod), [
            'sku' => 'PRD-TEST-002',
            'name' => 'Produk Produksi Updated',
            'price' => 80000,
            'unit' => 'kg',
            'stock_quantity' => 25,
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $prod->id,
            'name' => 'Produk Produksi Updated',
        ]);
    }

    public function test_sales_role_can_access_batch_detail_and_has_tambah_antrean_button(): void
    {
        $salesUser = User::factory()->create(['role' => UserRole::SALES]);
        $product = Product::create([
            'sku' => 'PRD-BATCH-001',
            'name' => 'Produk Batch',
            'price' => 10000,
            'unit' => 'kg',
            'stock_quantity' => 5,
        ]);

        $batch = ProductionBatch::create([
            'batch_number' => 'BATCH-TEST-01',
            'product_id' => $product->id,
            'target_quantity' => 10,
            'unit' => 'kg',
            'status' => 'in_progress',
            'stage' => 'Sortir',
            'start_date' => '2026-09-01',
            'pic_name' => 'Mandor',
        ]);

        $this->actingAs($salesUser);

        // Can view antrean page with "+ Tambah Antrean"
        $reqResponse = $this->get(route('production-requests.index'));
        $reqResponse->assertStatus(200);
        $reqResponse->assertSee('+ Tambah Antrean');
        $reqResponse->assertSee('/production/batches/1');

        // Can access /production/batches/1 (or $batch->id)
        $batchResponse = $this->get('/production/batches/'.$batch->id);
        $this->assertTrue(in_array($batchResponse->status(), [200, 302]));
    }
}
