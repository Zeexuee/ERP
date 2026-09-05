<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\MaterialReceipt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            [
                'code' => 'MAT-GHR-001',
                'name' => 'Kayu Dasar Medang Kering (Potongan)',
                'category' => 'Kayu Dasar',
                'unit' => 'kg',
                'stock_quantity' => 150,
                'minimum_stock' => 30,
                'unit_cost' => 250000.00,
            ],
            [
                'code' => 'MAT-GHR-002',
                'name' => 'Minyak Gaharu Murni (Dehn Al Oud Kalimantan)',
                'category' => 'Minyak & Resin',
                'unit' => 'tola',
                'stock_quantity' => 30,
                'minimum_stock' => 10,
                'unit_cost' => 1200000.00,
            ],
            [
                'code' => 'MAT-GHR-003',
                'name' => 'Resin Getah Gaharu Alami Merauke',
                'category' => 'Minyak & Resin',
                'unit' => 'kg',
                'stock_quantity' => 45,
                'minimum_stock' => 10,
                'unit_cost' => 1800000.00,
            ],
            [
                'code' => 'MAT-GHR-004',
                'name' => 'Serbuk Gaharu Super Saringan 40 Mesh',
                'category' => 'Serbuk & Perekat',
                'unit' => 'kg',
                'stock_quantity' => 200,
                'minimum_stock' => 50,
                'unit_cost' => 350000.00,
            ],
            [
                'code' => 'MAT-GHR-005',
                'name' => 'Perekat Alami Kulit Kayu (Sticky Wood Powder)',
                'category' => 'Serbuk & Perekat',
                'unit' => 'kg',
                'stock_quantity' => 100,
                'minimum_stock' => 25,
                'unit_cost' => 85000.00,
            ],
            [
                'code' => 'MAT-GHR-006',
                'name' => 'Kotak Eksklusif Bukhoor Press 500g',
                'category' => 'Kemasan & Wadah',
                'unit' => 'pcs',
                'stock_quantity' => 300,
                'minimum_stock' => 50,
                'unit_cost' => 15000.00,
            ],
            [
                'code' => 'MAT-GHR-007',
                'name' => 'Botol Kristal Tola Ukir 12ml',
                'category' => 'Kemasan & Wadah',
                'unit' => 'pcs',
                'stock_quantity' => 150,
                'minimum_stock' => 30,
                'unit_cost' => 35000.00,
            ],
        ];

        foreach ($materials as $mat) {
            $created = Material::updateOrCreate(['code' => $mat['code']], $mat);

            $rcvNum = 'RCV-'.str_replace('MAT-', '', $mat['code']).'-001';

            // Buat catatan riwayat barang masuk awal
            MaterialReceipt::create([
                'receipt_number' => $rcvNum,
                'material_id' => $created->id,
                'quantity' => $mat['stock_quantity'],
                'unit' => $mat['unit'],
                'unit_cost' => $mat['unit_cost'],
                'source_or_supplier' => 'Mitra Pemasok Hutan & Ekstraksi Al-Barakah',
                'received_date' => Carbon::now()->subDays(10)->toDateString(),
                'received_by' => 'Kepala Pabrik (Produksi)',
                'notes' => 'Penerimaan stok awal bahan baku pabrik Sentul.',
            ]);

            MaterialLog::create([
                'material_id' => $created->id,
                'type' => 'in',
                'reference_number' => $rcvNum,
                'quantity' => $mat['stock_quantity'],
                'unit' => $mat['unit'],
                'actor_by' => 'Kepala Pabrik (Produksi)',
                'source_or_destination' => 'Mitra Pemasok Hutan & Ekstraksi Al-Barakah',
                'movement_date' => Carbon::now()->subDays(10),
                'notes' => 'Penerimaan stok awal bahan baku pabrik Sentul.',
            ]);
        }
    }
}
