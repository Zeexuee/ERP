<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_displays_products_and_branch_specifications_breakdown(): void
    {
        $product = Product::create([
            'sku' => 'PRD-TEST-BRANCH',
            'name' => 'Produk Multifungsi Smart',
            'price' => 7500000,
            'stock_quantity' => 25,
        ]);

        ProductBranch::create([
            'product_id' => $product->id,
            'branch_name' => 'Pabrik Cikarang (Main Plant)',
            'branch_code' => 'PLANT-CKR',
            'quantity' => 15,
            'specification_notes' => 'Spek: Tegangan 220V, Finishing Anodized Black',
        ]);

        ProductBranch::create([
            'product_id' => $product->id,
            'branch_name' => 'Plant Surabaya (East Hub)',
            'branch_code' => 'PLANT-SBY',
            'quantity' => 10,
            'specification_notes' => 'Spek: Tegangan 220V, Finishing Silver Matte',
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('PRD-TEST-BRANCH');
        $response->assertSee('Produk Multifungsi Smart');
        $response->assertSee('25 unit');
        $response->assertSee('PLANT-CKR');
        $response->assertSee('15 unit');
        $response->assertSee('Finishing Anodized Black');
        $response->assertSee('PLANT-SBY');
        $response->assertSee('10 unit');
        $response->assertSee('Finishing Silver Matte');
    }
}
