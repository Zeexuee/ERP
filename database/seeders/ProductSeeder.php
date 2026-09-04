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
                'sku' => 'GHR-SNI-001',
                'name' => 'Kayu Gaharu Sana\'i Super Malaki',
                'price' => 4500000.00,
                'stock_quantity' => 85,
                'unit' => 'kg',
                'branches' => [
                    [
                        'branch_name' => 'Gudang Utama Jakarta',
                        'branch_code' => 'WH-JKT',
                        'quantity' => 35,
                        'specification_notes' => 'Kepingan kayu gaharu sana\'i supergrade infusi resin murni Merauke & Kalimantan. Aroma manis, balsamic abadi, asap putih tebal tanpa jelaga.',
                    ],
                    [
                        'branch_name' => 'Pabrik Pengolahan Sentul',
                        'branch_code' => 'PLANT-STL',
                        'quantity' => 50,
                        'specification_notes' => 'Batch ekspor kualitas istimewa, kadar getah resin 65%. Kemasan vacuum foil sealed kedap udara 1 kg.',
                    ],
                ],
            ],
            [
                'sku' => 'GHR-SNI-002',
                'name' => 'Kayu Gaharu Sana\'i Maroke Grade A',
                'price' => 2800000.00,
                'stock_quantity' => 65,
                'unit' => 'kg',
                'branches' => [
                    [
                        'branch_name' => 'Gudang Utama Jakarta',
                        'branch_code' => 'WH-JKT',
                        'quantity' => 40,
                        'specification_notes' => 'Kepingan olahan gaharu sana\'i beraroma khas Maroke Papua, nada wangi hangat rempah madu. Sangat cocok untuk majelis & masjid.',
                    ],
                    [
                        'branch_name' => 'Depot Distribusi Surabaya',
                        'branch_code' => 'WH-SBY',
                        'quantity' => 25,
                        'specification_notes' => 'Potongan tebal 3-5mm, proses vacuum pressure resin Maroke alami murni.',
                    ],
                ],
            ],
            [
                'sku' => 'GHR-SNI-003',
                'name' => 'Kayu Gaharu Sana\'i Kalbar Tiger Motif',
                'price' => 5200000.00,
                'stock_quantity' => 35,
                'unit' => 'kg',
                'branches' => [
                    [
                        'branch_name' => 'Gudang Utama Jakarta',
                        'branch_code' => 'WH-JKT',
                        'quantity' => 20,
                        'specification_notes' => 'Motif loreng harimau hitam pekat mengkilap (tiger stripes), resin padat. Karakter aroma mewah pekat khas Timur Tengah.',
                    ],
                    [
                        'branch_name' => 'Pabrik Pengolahan Sentul',
                        'branch_code' => 'PLANT-STL',
                        'quantity' => 15,
                        'specification_notes' => 'Grade kolektor VIP, finishing hand-carved natural edge, sertifikasi uji lab karantina tumbuhan.',
                    ],
                ],
            ],
            [
                'sku' => 'GHR-SNI-004',
                'name' => 'Gaharu Sana\'i Chip Bukhoor Press',
                'price' => 650000.00,
                'stock_quantity' => 130,
                'unit' => 'kotak',
                'branches' => [
                    [
                        'branch_name' => 'Pabrik Pengolahan Sentul',
                        'branch_code' => 'PLANT-STL',
                        'quantity' => 80,
                        'specification_notes' => 'Briket keping press serbuk gaharu sana\'i murni isi 500 gram per kotak. Siap dibakar pada mabkhara arang maupun burner elektrik.',
                    ],
                    [
                        'branch_name' => 'Depot Distribusi Surabaya',
                        'branch_code' => 'WH-SBY',
                        'quantity' => 50,
                        'specification_notes' => 'Ramuan formula infusi minyak gaharu buaya dan ambar wangi semerbak lembut.',
                    ],
                ],
            ],
            [
                'sku' => 'GHR-SNI-005',
                'name' => 'Dupa Stick Gaharu Sana\'i Herbal (Tanpa Bambu)',
                'price' => 185000.00,
                'stock_quantity' => 200,
                'unit' => 'pack',
                'branches' => [
                    [
                        'branch_name' => 'Pabrik Pengolahan Sentul',
                        'branch_code' => 'PLANT-STL',
                        'quantity' => 120,
                        'specification_notes' => 'Dupa stick murni 100% serbuk gaharu sana\'i tanpa lidi bambu. Isi 50 batang, durasi pembakaran 45 menit per batang.',
                    ],
                    [
                        'branch_name' => 'Gudang Utama Jakarta',
                        'branch_code' => 'WH-JKT',
                        'quantity' => 80,
                        'specification_notes' => 'Formula herbal alami bebas zat kimia sintesis, nyaman untuk ruangan tertutup ber-AC.',
                    ],
                ],
            ],
            [
                'sku' => 'GHR-SNI-006',
                'name' => 'Minyak Gaharu Murni (Dehn Al Oud) 1 Tola',
                'price' => 1750000.00,
                'stock_quantity' => 50,
                'unit' => 'tola',
                'branches' => [
                    [
                        'branch_name' => 'Gudang Utama Jakarta',
                        'branch_code' => 'WH-JKT',
                        'quantity' => 30,
                        'specification_notes' => 'Minyak suling atsiri murni gaharu kemasan botol kristal 12ml (1 tola). Bahan baku utama pembuatan dan perendam Kayu Gaharu Sana\'i.',
                    ],
                    [
                        'branch_name' => 'Pabrik Pengolahan Sentul',
                        'branch_code' => 'PLANT-STL',
                        'quantity' => 20,
                        'specification_notes' => 'Longevity aroma pada kain/busana bertahan lebih dari 48 jam.',
                    ],
                ],
            ],
            [
                'sku' => 'GHR-SNI-007',
                'name' => 'Serbuk Gaharu Sana\'i Kasar (Raw Material)',
                'price' => 850000.00,
                'stock_quantity' => 150,
                'unit' => 'kg',
                'branches' => [
                    [
                        'branch_name' => 'Pabrik Pengolahan Sentul',
                        'branch_code' => 'PLANT-STL',
                        'quantity' => 150,
                        'specification_notes' => 'Serbuk kayu gaharu sana\'i saringan 40 mesh siap kempa cetak bukhoor atau infusi minyak wangi.',
                    ],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $branches = $productData['branches'] ?? [];
            unset($productData['branches']);

            $product = Product::updateOrCreate(['sku' => $productData['sku']], $productData);

            if (! empty($branches)) {
                $product->branches()->delete();
                foreach ($branches as $branch) {
                    $product->branches()->create($branch);
                }
                // Sync total stock quantity
                $product->update(['stock_quantity' => $product->branches()->sum('quantity')]);
            }
        }
    }
}
