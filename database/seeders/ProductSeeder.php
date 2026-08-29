<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'PRD-001',
                'name' => 'Laptop Corporate Elite 15"',
                'price' => 15000000.00,
                'stock_quantity' => 25,
            ],
            [
                'sku' => 'PRD-002',
                'name' => 'Monitor Ergonomis 27" 4K',
                'price' => 5500000.00,
                'stock_quantity' => 40,
            ],
            [
                'sku' => 'PRD-003',
                'name' => 'Keyboard Mekanikal Wireless',
                'price' => 1250000.00,
                'stock_quantity' => 100,
            ],
            [
                'sku' => 'PRD-004',
                'name' => 'Mouse Presisi Ergonomis',
                'price' => 750000.00,
                'stock_quantity' => 150,
            ],
            [
                'sku' => 'PRD-005',
                'name' => 'Docking Station USB-C Dual Display',
                'price' => 2200000.00,
                'stock_quantity' => 10, // low stock for testing production trigger
            ],
            [
                'sku' => 'PRD-006',
                'name' => 'Server Rack Cabinet 42U',
                'price' => 35000000.00,
                'stock_quantity' => 2,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
