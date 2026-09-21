<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialCarveBatch;
use App\Models\MaterialCarveItem;
use App\Models\MaterialLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class MaterialCarveService
{
    /**
     * Inisiasi proses potong ukir mandiri (Potong stok bahan mentah asal, catat log, & buat batch status in_progress).
     */
    public function initiateCarveBatch(array $data): MaterialCarveBatch
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

            // Generate Kode Potong Ukir CRV-YYYYMM-XXXX
            $latest = MaterialCarveBatch::latest('id')->first();
            $carveCode = 'CRV-'.date('Ym').'-'.str_pad(($latest ? $latest->id + 1 : 1), 4, '0', STR_PAD_LEFT);

            // Kurangi stok bahan mentah asal di gudang
            $sourceMaterial->decrement('stock_quantity', $initialWeight);
            $sourceMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['pic_name'] ?? 'Sistem',
            ]);

            // Catat log alur keluar bahan mentah
            MaterialLog::create([
                'material_id' => $sourceMaterial->id,
                'type' => 'out',
                'reference_number' => $carveCode,
                'quantity' => $initialWeight,
                'unit' => $sourceMaterial->unit,
                'actor_by' => $data['pic_name'] ?? 'Sistem',
                'source_or_destination' => 'Inisiasi Potong Ukir',
                'movement_date' => $data['carve_date'] ?? now(),
                'notes' => $data['notes'] ?? 'Pengeluaran bahan baku mentah untuk proses potong ukir.',
                'signature_path' => $signaturePath,
            ]);

            // Buat record batch potong ukir (status in_progress)
            return MaterialCarveBatch::create([
                'carve_code' => $carveCode,
                'status' => 'in_progress',
                'source_material_id' => $sourceMaterial->id,
                'initial_weight' => $initialWeight,
                'dried_weight' => null,
                'drying_loss_weight' => 0,
                'carve_date' => $data['carve_date'] ?? now(),
                'pic_name' => $data['pic_name'] ?? 'Sistem',
                'notes' => $data['notes'] ?? null,
                'signature_path' => $signaturePath,
            ]);
        });
    }

    /**
     * Catat laporan hasil potong ukir (Report Carving): input berat kering, susut, pembagian item, dan tambahkan ke stok gudang.
     */
    public function submitCarveReport(MaterialCarveBatch $carveBatch, array $data): MaterialCarveBatch
    {
        return DB::transaction(function () use ($carveBatch, $data) {
            if ($carveBatch->isCompleted()) {
                throw new InvalidArgumentException("Batch potong ukir {$carveBatch->carve_code} sudah pernah diselesaikan dan dilaporkan.");
            }

            // Validasi item hasil potong ukir
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new InvalidArgumentException('Minimal harus ada 1 jenis hasil bahan dari potong ukir.');
            }

            $totalResultWeight = 0;
            foreach ($items as $item) {
                $totalResultWeight += (float) ($item['result_weight'] ?? 0);
            }

            $driedWeight = $totalResultWeight;
            $initialWeight = (float) $carveBatch->initial_weight;

            if ($driedWeight <= 0) {
                throw new InvalidArgumentException('Total berat hasil potong ukir harus lebih besar dari 0.');
            }

            if ($driedWeight > $initialWeight) {
                throw new InvalidArgumentException("Total berat hasil potong ukir ({$driedWeight} kg) tidak boleh melebihi berat bahan baku awal ({$initialWeight} kg).");
            }

            $dryingLoss = round($initialWeight - $driedWeight, 2);

            // Simpan Tanda Tangan Pelapor jika ada
            $reportSignaturePath = $this->saveSignature($data['report_signature_data'] ?? $data['signature_data'] ?? null, 'sig_report');

            $reportPic = $data['report_pic_name'] ?? $data['pic_name'] ?? $carveBatch->pic_name ?? 'Sistem';
            $reportDate = $data['report_date'] ?? now();

            // Perbarui status batch menjadi completed
            $carveBatch->update([
                'status' => 'completed',
                'dried_weight' => $driedWeight,
                'drying_loss_weight' => $dryingLoss,
                'report_date' => $reportDate,
                'report_pic_name' => $reportPic,
                'report_signature_path' => $reportSignaturePath,
            ]);

            $sourceMaterialName = $carveBatch->sourceMaterial?->name ?? 'Bahan Mentah';
            $sourceUnit = strtolower(trim($carveBatch->sourceMaterial?->unit ?? 'kg'));

            // Validasi satuan setiap item hasil potong ukir harus sama dengan bahan baku asal
            foreach ($items as $item) {
                if (empty($item['target_material_id'])) {
                    continue;
                }
                $targetMaterial = Material::findOrFail($item['target_material_id']);
                $targetUnit = strtolower(trim($targetMaterial->unit));

                if ($targetUnit !== $sourceUnit) {
                    throw new InvalidArgumentException(
                        "Satuan bahan hasil potong ukir [{$targetMaterial->code}] {$targetMaterial->name} ({$targetMaterial->unit}) tidak sesuai dengan satuan bahan mentah asal ({$carveBatch->sourceMaterial?->unit}). Pembagian hasil potong ukir harus menggunakan satuan yang sama."
                    );
                }
            }

            // Simpan setiap item hasil potong ukir & tambahkan ke stok bahan siap di gudang
            foreach ($items as $item) {
                $targetMaterial = Material::findOrFail($item['target_material_id']);
                $resultWeight = (float) $item['result_weight'];

                if ($resultWeight <= 0) {
                    continue;
                }

                MaterialCarveItem::create([
                    'material_carve_batch_id' => $carveBatch->id,
                    'target_material_id' => $targetMaterial->id,
                    'result_weight' => $resultWeight,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Tambahkan stok bahan di gudang
                $targetMaterial->increment('stock_quantity', $resultWeight);
                $targetMaterial->update([
                    'last_weighed_at' => now(),
                    'last_weighed_by' => $reportPic,
                ]);

                // Catat log alur masuk untuk bahan hasil potong ukir
                MaterialLog::create([
                    'material_id' => $targetMaterial->id,
                    'type' => 'in',
                    'reference_number' => $carveBatch->carve_code,
                    'quantity' => $resultWeight,
                    'unit' => $targetMaterial->unit,
                    'actor_by' => $reportPic,
                    'source_or_destination' => "Hasil Potong Ukir dari {$sourceMaterialName}",
                    'movement_date' => $reportDate,
                    'notes' => $item['notes'] ?? 'Penerimaan bahan hasil potong ukir.',
                    'signature_path' => $reportSignaturePath,
                ]);
            }

            return $carveBatch->fresh(['sourceMaterial', 'items.targetMaterial']);
        });
    }

    /**
     * Simpan proses potong ukir (backward-compatible: inisiasi + laporan jika items disediakan).
     */
    public function createCarveBatch(array $data): MaterialCarveBatch
    {
        $batch = $this->initiateCarveBatch($data);

        if (! empty($data['items']) && ! empty($data['dried_weight'])) {
            return $this->submitCarveReport($batch, $data);
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
                    $filename = 'material-carves/signatures/'.$prefix.'_'.time().'_'.uniqid().'.png';
                    Storage::disk('public')->put($filename, $decodedImage);

                    return $filename;
                }
            }
        }

        return null;
    }
}
