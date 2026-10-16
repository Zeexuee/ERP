<?php

namespace Database\Seeders;

use App\Models\WarehouseBranch;
use App\Models\WarehouseItem;
use App\Models\WarehouseProduct;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Standalone STIN Production Branches (menandakan kapan produk dibuat)
        $branches = [
            [
                'code' => '10/09',
                'name' => 'Produksi 10/09',
                'description' => 'Batch produksi wewangian tanggal 10 September 2026',
                'is_active' => true,
            ],
            [
                'code' => '11/09',
                'name' => 'Produksi 11/09',
                'description' => 'Batch produksi wewangian tanggal 11 September 2026',
                'is_active' => true,
            ],
            [
                'code' => '12/09',
                'name' => 'Produksi 12/09',
                'description' => 'Batch produksi wewangian tanggal 12 September 2026',
                'is_active' => true,
            ],
        ];

        $branchModels = [];
        foreach ($branches as $branchData) {
            $branchModels[$branchData['code']] = WarehouseBranch::updateOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
        }

        // 2. Master Produk Wewangian STIN (Parfum, Attar, Bukhoor, Body Mist)
        $perfumeProducts = [
            [
                'name' => 'Oud Royale Extrait de Parfum 50ml',
                'unit' => 'botol',
                'unit_price' => 450000,
                'category' => 'Extrait de Parfum',
                'description' => 'Top: Bergamot, Pink Pepper; Mid: Rose Taif; Base: Pure Oud Kalimantan',
            ],
            [
                'name' => 'Rose Amber Attar 12ml',
                'unit' => 'botol',
                'unit_price' => 280000,
                'category' => 'Attar / Minyak Wangi Konsentrat',
                'description' => 'Minyak wangi murni non-alkohol aroma mawar damaskus dan amber hangat',
            ],
            [
                'name' => 'Musk Al-Lail Concentrated Perfume 15ml',
                'unit' => 'botol',
                'unit_price' => 210000,
                'category' => 'Attar / Minyak Wangi Konsentrat',
                'description' => 'Aroma kasturi malam eksotis dengan sentuhan rempah aromatik oriental',
            ],
            [
                'name' => 'Vanilla Sandalwood Body Mist 100ml',
                'unit' => 'botol',
                'unit_price' => 135000,
                'category' => 'Body Mist / Fragrance Spray',
                'description' => 'Spray wewangian tubuh segar perpaduan vanila manis dan kayu cendana lembut',
            ],
            [
                'name' => 'Bukhoor Oud Bukhiyah 250g',
                'unit' => 'box',
                'unit_price' => 175000,
                'category' => 'Bukhoor / Dupa Wewangian',
                'description' => 'Serpihan kayu gaharu basah rendaman parfum aromatik untuk pengharum ruangan',
            ],
        ];

        $productModels = [];
        foreach ($perfumeProducts as $prodData) {
            $productModels[] = WarehouseProduct::updateOrCreate(
                ['name' => $prodData['name']],
                $prodData
            );
        }

        // 3. Clean old items to make STIN data completely standalone
        WarehouseItem::query()->forceDelete();

        // 4. Input Barang ke Branch (Alokasi stok per batch branch dengan Barcode 1D & 2D)
        $allocations = [
            ['prod_idx' => 0, 'branch_code' => '10/09', 'stock' => 85, 'pic' => 'Ahmad Syahputra'],
            ['prod_idx' => 1, 'branch_code' => '10/09', 'stock' => 60, 'pic' => 'Ahmad Syahputra'],
            ['prod_idx' => 2, 'branch_code' => '10/09', 'stock' => 45, 'pic' => 'Ahmad Syahputra'],
            ['prod_idx' => 0, 'branch_code' => '11/09', 'stock' => 70, 'pic' => 'Siti Rahmawati'],
            ['prod_idx' => 3, 'branch_code' => '11/09', 'stock' => 110, 'pic' => 'Siti Rahmawati'],
            ['prod_idx' => 4, 'branch_code' => '11/09', 'stock' => 50, 'pic' => 'Siti Rahmawati'],
            ['prod_idx' => 1, 'branch_code' => '12/09', 'stock' => 40, 'pic' => 'Dedi Kurniawan'],
            ['prod_idx' => 2, 'branch_code' => '12/09', 'stock' => 55, 'pic' => 'Dedi Kurniawan'],
            ['prod_idx' => 3, 'branch_code' => '12/09', 'stock' => 90, 'pic' => 'Dedi Kurniawan'],
        ];

        $sampleSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAK8AAAA8CAYAAAD9y2v2AAAACXBIWXMAAAsTAAALEwEAmpwYAAAFmElEQVR4nO3df2hVZRzH8ffN3Zy21fyg0qZl1jL9E83ULdMWaVlqZkZpWVpaqVlZWVqWlpWVlaVlqWlpWVpaVlqWlpWVlaVlqWlpWVpaVlqWlpWVlaVlqWlpWVpaVlqW';

        foreach ($allocations as $idx => $alloc) {
            $product = $productModels[$alloc['prod_idx']];
            $branch = $branchModels[$alloc['branch_code']];
            $cleanCode = preg_replace('/[^0-9]/', '', $branch->code);
            $seq = str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);

            $sku = "ITM-{$cleanCode}-{$seq}";

            $item = WarehouseItem::create([
                'warehouse_product_id' => $product->id,
                'name' => $product->name,
                'sku' => $sku,
                'barcode' => $sku,
                'qr_code' => $sku,
                'production_branch' => $branch->name,
                'warehouse_branch_id' => $branch->id,
                'category' => $product->category,
                'unit' => $product->unit,
                'unit_price' => $product->unit_price,
                'current_stock' => $alloc['stock'],
                'description' => $product->description,
                'pic_name' => $alloc['pic'],
                'signature_path' => $sampleSignature,
                'is_active' => true,
            ]);

            // Log initial stock movement
            $item->addStock(
                $alloc['stock'],
                'Inisiasi alokasi stok batch '.$branch->name,
                'initial',
                null,
                $alloc['pic'],
                $sampleSignature
            );
            $item->update(['current_stock' => $alloc['stock']]);
        }
    }
}
