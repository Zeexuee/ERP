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
     * Inisiasi proses sortir kayu mandiri (Potong stok bahan mentah asal, catat log, & buat batch status in_progress).
     */
    public function initiateSortBatch(array $data): MaterialSortBatch
    {
        return DB::transaction(function () use ($data) {
            $sourceMaterial = Material::findOrFail($data['source_material_id']);
            $initialWeight = (float) $data['initial_weight'];

            if ($sourceMaterial->stock_quantity < $initialWeight) {
                throw new InvalidArgumentException(
                    "Stok bahan mentah {$sourceMaterial->name} tidak mencukupi (Tersedia: {$sourceMaterial->stock_quantity} {$sourceMaterial->unit}, Dibutuhkan: {$initialWeight} {$sourceMaterial->unit})."
                );
            }

            if ($initialWeight <= 0) {
                throw new InvalidArgumentException('Berat bahan awal harus lebih besar dari 0.');
            }

            // Simpan Tanda Tangan jika ada
            $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_init');

            // Generate Kode Sortir SRT-YYYYMM-XXXX
            $latest = MaterialSortBatch::latest('id')->first();
            $sortCode = 'SRT-'.date('Ym').'-'.str_pad(($latest ? $latest->id + 1 : 1), 4, '0', STR_PAD_LEFT);

            // Kurangi stok bahan mentah asal di gudang
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
                'source_or_destination' => 'Inisiasi Sortir Kayu',
                'movement_date' => $data['sort_date'] ?? now(),
                'notes' => $data['notes'] ?? 'Pengeluaran bahan baku mentah untuk proses sortir kayu.',
                'signature_path' => $signaturePath,
            ]);

            // Buat record batch sortir (status in_progress)
            return MaterialSortBatch::create([
                'sort_code' => $sortCode,
                'status' => 'in_progress',
                'source_material_id' => $sourceMaterial->id,
                'initial_weight' => $initialWeight,
                'dried_weight' => null,
                'drying_loss_weight' => 0,
                'sort_date' => $data['sort_date'] ?? now(),
                'pic_name' => $data['pic_name'],
                'notes' => $data['notes'] ?? null,
                'signature_path' => $signaturePath,
            ]);
        });
    }

    /**
     * Catat laporan hasil sortir (Report Sorting): input berat kering, susut, pembagian item, dan tambahkan ke stok gudang.
     */
    public function submitSortReport(MaterialSortBatch $sortBatch, array $data): MaterialSortBatch
    {
        return DB::transaction(function () use ($sortBatch, $data) {
            if ($sortBatch->isCompleted()) {
                throw new InvalidArgumentException("Batch sortir {$sortBatch->sort_code} sudah pernah diselesaikan dan dilaporkan.");
            }

            $driedWeight = (float) $data['dried_weight'];
            $initialWeight = (float) $sortBatch->initial_weight;

            if ($driedWeight <= 0) {
                throw new InvalidArgumentException('Berat hasil sortir harus lebih besar dari 0.');
            }

            if ($driedWeight > $initialWeight) {
                throw new InvalidArgumentException("Berat hasil sortir ({$driedWeight} kg) tidak boleh melebihi berat bahan baku awal ({$initialWeight} kg).");
            }

            $dryingLoss = round($initialWeight - $driedWeight, 2);

            // Validasi item hasil sortir
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new InvalidArgumentException('Minimal harus ada 1 jenis hasil sortir bahan kayu tembak.');
            }

            $totalResultWeight = 0;
            foreach ($items as $item) {
                $totalResultWeight += (float) ($item['result_weight'] ?? 0);
            }

            // Toleransi selisih pembulatan 0.1 kg
            if (abs($totalResultWeight - $driedWeight) > 0.1) {
                throw new InvalidArgumentException(
                    "Total berat hasil sortir ({$totalResultWeight} kg) harus sama dengan berat hasil sortir ({$driedWeight} kg)."
                );
            }

            // Simpan Tanda Tangan Pelapor jika ada
            $reportSignaturePath = $this->saveSignature($data['report_signature_data'] ?? $data['signature_data'] ?? null, 'sig_report');

            $reportPic = $data['report_pic_name'] ?? $data['pic_name'] ?? $sortBatch->pic_name;
            $reportDate = $data['report_date'] ?? now();

            // Perbarui status batch menjadi completed
            $sortBatch->update([
                'status' => 'completed',
                'dried_weight' => $driedWeight,
                'drying_loss_weight' => $dryingLoss,
                'report_date' => $reportDate,
                'report_pic_name' => $reportPic,
                'report_signature_path' => $reportSignaturePath,
            ]);

            $sourceMaterialName = $sortBatch->sourceMaterial?->name ?? 'Bahan Mentah';
            $sourceUnit = strtolower(trim($sortBatch->sourceMaterial?->unit ?? 'kg'));

            // Validasi satuan setiap item hasil sortir harus sama dengan bahan baku asal
            foreach ($items as $item) {
                if (empty($item['target_material_id'])) {
                    continue;
                }
                $targetMaterial = Material::findOrFail($item['target_material_id']);
                $targetUnit = strtolower(trim($targetMaterial->unit));

                if ($targetUnit !== $sourceUnit) {
                    throw new InvalidArgumentException(
                        "Satuan bahan hasil sortir [{$targetMaterial->code}] {$targetMaterial->name} ({$targetMaterial->unit}) tidak sesuai dengan satuan bahan mentah asal ({$sortBatch->sourceMaterial?->unit}). Pembagian hasil sortir harus menggunakan satuan yang sama."
                    );
                }
            }

            // Simpan setiap item hasil sortir & tambahkan ke stok bahan siap tembak di gudang
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
                    'last_weighed_by' => $reportPic,
                ]);

                // Catat log alur masuk untuk bahan hasil sortir
                MaterialLog::create([
                    'material_id' => $targetMaterial->id,
                    'type' => 'in',
                    'reference_number' => $sortBatch->sort_code,
                    'quantity' => $resultWeight,
                    'unit' => $targetMaterial->unit,
                    'actor_by' => $reportPic,
                    'source_or_destination' => "Hasil Sortir dari {$sourceMaterialName}",
                    'movement_date' => $reportDate,
                    'notes' => $item['notes'] ?? 'Penerimaan bahan kayu siap tembak hasil sortir.',
                    'signature_path' => $reportSignaturePath,
                ]);
            }

            return $sortBatch->fresh(['sourceMaterial', 'items.targetMaterial']);
        });
    }

    /**
     * Simpan proses sortir kayu (backward-compatible: inisiasi + laporan jika items disediakan).
     */
    public function createSortBatch(array $data): MaterialSortBatch
    {
        $batch = $this->initiateSortBatch($data);

        if (! empty($data['items']) && ! empty($data['dried_weight'])) {
            return $this->submitSortReport($batch, $data);
        }

        return $batch;
    }

    /**
     * Simpan string base64 gambar tanda tangan ke public storage.
     */
    protected function saveSignature(?string $signatureData, string $prefix): ?string
    {
        if (! empty($signatureData) && str_contains($signatureData, 'base64,')) {
            $base64Data = explode('base64,', $signatureData)[1] ?? null;
            if ($base64Data) {
                $decodedImage = base64_decode($base64Data);
                if ($decodedImage !== false) {
                    $filename = 'material-sorts/signatures/'.$prefix.'_'.time().'_'.uniqid().'.png';
                    Storage::disk('public')->put($filename, $decodedImage);

                    return $filename;
                }
            }
        }

        return null;
    }
}
