<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\MaterialSortBatch;
use App\Models\MaterialSortItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class MaterialSortService
{
    /**
     * Simpan proses sortir kayu independen (Bahan Mentah -> Jemur -> Sortir Multi-Grade -> Masuk Gudang).
     */
    public function createSortBatch(array $data): MaterialSortBatch
    {
        return DB::transaction(function () use ($data) {
            $sourceMaterial = Material::findOrFail($data['source_material_id']);
            $initialWeight = (float) $data['initial_weight'];
            $driedWeight = (float) $data['dried_weight'];

            if ($sourceMaterial->stock_quantity < $initialWeight) {
                throw new InvalidArgumentException(
                    "Stok bahan mentah {$sourceMaterial->name} tidak mencukupi (Tersedia: {$sourceMaterial->stock_quantity} {$sourceMaterial->unit}, Dibutuhkan: {$initialWeight} {$sourceMaterial->unit})."
                );
            }

            if ($driedWeight > $initialWeight) {
                throw new InvalidArgumentException('Berat setelah jemur tidak boleh melebihi berat bahan baku awal.');
            }

            $dryingLoss = round($initialWeight - $driedWeight, 2);

            // Validasi total berat hasil sortir
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new InvalidArgumentException('Minimal harus ada 1 jenis hasil sortir bahan kayu tembak.');
            }

            $totalResultWeight = 0;
            foreach ($items as $item) {
                $totalResultWeight += (float) ($item['result_weight'] ?? 0);
            }

            // Izinkan toleransi pembulatan 0.05 kg
            if (abs($totalResultWeight - $driedWeight) > 0.1) {
                throw new InvalidArgumentException(
                    "Total berat hasil sortir ({$totalResultWeight} kg) harus sama dengan berat setelah penjemuran ({$driedWeight} kg)."
                );
            }

            // Simpan Tanda Tangan jika ada
            $signaturePath = null;
            if (! empty($data['signature_data']) && str_contains($data['signature_data'], 'base64,')) {
                $base64Data = explode('base64,', $data['signature_data'])[1] ?? null;
                if ($base64Data) {
                    $decodedImage = base64_decode($base64Data);
                    $filename = 'material-sorts/signatures/sig_sort_'.time().'_'.uniqid().'.png';
                    Storage::disk('public')->put($filename, $decodedImage);
                    $signaturePath = $filename;
                }
            }

            // Generate Kode Sortir SRT-YYYYMM-XXXX
            $latest = MaterialSortBatch::latest('id')->first();
            $sortCode = 'SRT-'.date('Ym').'-'.str_pad(($latest ? $latest->id + 1 : 1), 4, '0', STR_PAD_LEFT);

            // Kurangi stok bahan mentah asal
            $sourceMaterial->decrement('stock_quantity', $initialWeight);
            $sourceMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['pic_name'],
            ]);

            // Catat log alur keluar bahan mentah
            MaterialLog::create([
                'material_id' => $sourceMaterial->id,
                'type' => 'out',
                'reference_number' => $sortCode,
                'quantity' => $initialWeight,
                'unit' => $sourceMaterial->unit,
                'actor_by' => $data['pic_name'],
                'source_or_destination' => 'Proses Jemur & Sortir Kayu Tembak',
                'movement_date' => $data['sort_date'] ?? now(),
                'notes' => "Pengurangan bahan baku mentah untuk sortir. Susut jemur: {$dryingLoss} {$sourceMaterial->unit}.",
                'signature_path' => $signaturePath,
            ]);

            // Buat record batch sortir
            $sortBatch = MaterialSortBatch::create([
                'sort_code' => $sortCode,
                'source_material_id' => $sourceMaterial->id,
                'initial_weight' => $initialWeight,
                'dried_weight' => $driedWeight,
                'drying_loss_weight' => $dryingLoss,
                'sort_date' => $data['sort_date'] ?? now(),
                'pic_name' => $data['pic_name'],
                'notes' => $data['notes'] ?? null,
                'signature_path' => $signaturePath,
            ]);

            // Simpan setiap item hasil sortir & tambahkan ke stok bahan siap tembak
            foreach ($items as $item) {
                $targetMaterial = Material::findOrFail($item['target_material_id']);
                $resultWeight = (float) $item['result_weight'];

                if ($resultWeight <= 0) {
                    continue;
                }

                MaterialSortItem::create([
                    'material_sort_batch_id' => $sortBatch->id,
                    'target_material_id' => $targetMaterial->id,
                    'result_weight' => $resultWeight,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Tambahkan stok bahan tembak di gudang
                $targetMaterial->increment('stock_quantity', $resultWeight);
                $targetMaterial->update([
                    'last_weighed_at' => now(),
                    'last_weighed_by' => $data['pic_name'],
                ]);

                // Catat log alur masuk untuk bahan hasil sortir
                MaterialLog::create([
                    'material_id' => $targetMaterial->id,
                    'type' => 'in',
                    'reference_number' => $sortCode,
                    'quantity' => $resultWeight,
                    'unit' => $targetMaterial->unit,
                    'actor_by' => $data['pic_name'],
                    'source_or_destination' => "Hasil Sortir dari {$sourceMaterial->name}",
                    'movement_date' => $data['sort_date'] ?? now(),
                    'notes' => $item['notes'] ?? 'Penerimaan bahan kayu siap tembak hasil sortir.',
                    'signature_path' => $signaturePath,
                ]);
            }

            return $sortBatch->load(['sourceMaterial', 'items.targetMaterial']);
        });
    }
}
