<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\ProductionTembakBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ProductionTembakService
{
    /**
     * Inisiasi proses tembak kayu (Potong stok kayu & minyak/resin, catat log, & buat batch status in_progress).
     */
    public function initiateTembakBatch(array $data): ProductionTembakBatch
    {
        return DB::transaction(function () use ($data) {
            $woodMaterial = Material::findOrFail($data['wood_material_id']);
            $woodWeight = (float) $data['wood_weight'];

            $resinMaterial = Material::findOrFail($data['resin_material_id']);
            $resinWeight = (float) $data['resin_weight'];

            if ($woodWeight <= 0) {
                throw new InvalidArgumentException('Berat kayu awal harus lebih besar dari 0.');
            }

            if ($woodMaterial->stock_quantity < $woodWeight) {
                throw new InvalidArgumentException(
                    "Stok kayu {$woodMaterial->name} tidak mencukupi (Tersedia: {$woodMaterial->stock_quantity} {$woodMaterial->unit}, Dibutuhkan: {$woodWeight} {$woodMaterial->unit})."
                );
            }

            if ($resinWeight <= 0) {
                throw new InvalidArgumentException('Kuantitas minyak/resin harus lebih besar dari 0.');
            }

            if ($resinMaterial->stock_quantity < $resinWeight) {
                throw new InvalidArgumentException(
                    "Stok resin {$resinMaterial->name} tidak mencukupi (Tersedia: {$resinMaterial->stock_quantity} {$resinMaterial->unit}, Dibutuhkan: {$resinWeight} {$resinMaterial->unit})."
                );
            }

            // Simpan tanda tangan digital jika ada
            $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_tbk_init');

            // Generate Kode Tembak TBK-YYYYMM-XXXX
            $latest = ProductionTembakBatch::latest('id')->first();
            $tembakCode = 'TBK-'.date('Ym').'-'.str_pad(($latest ? $latest->id + 1 : 1), 4, '0', STR_PAD_LEFT);

            // Potong stok kayu di gudang & catat log
            $woodMaterial->decrement('stock_quantity', $woodWeight);
            $woodMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['pic_name'],
            ]);

            MaterialLog::create([
                'material_id' => $woodMaterial->id,
                'type' => 'out',
                'reference_number' => $tembakCode,
                'quantity' => $woodWeight,
                'unit' => $woodMaterial->unit,
                'actor_by' => $data['pic_name'],
                'source_or_destination' => "Inisiasi Tembak #{$tembakCode}",
                'movement_date' => $data['tembak_date'] ?? now(),
                'notes' => $data['notes'] ?? "Pengeluaran bahan kayu ({$woodMaterial->name}) untuk proses tembak.",
                'signature_path' => $signaturePath,
            ]);

            // Potong stok resin di gudang & catat log
            $resinMaterial->decrement('stock_quantity', $resinWeight);
            $resinMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['pic_name'],
            ]);

            MaterialLog::create([
                'material_id' => $resinMaterial->id,
                'type' => 'out',
                'reference_number' => $tembakCode,
                'quantity' => $resinWeight,
                'unit' => $resinMaterial->unit,
                'actor_by' => $data['pic_name'],
                'source_or_destination' => "Inisiasi Tembak #{$tembakCode}",
                'movement_date' => $data['tembak_date'] ?? now(),
                'notes' => $data['notes'] ?? "Pengeluaran minyak/resin ({$resinMaterial->name}) untuk proses tembak.",
                'signature_path' => $signaturePath,
            ]);

            // Buat record batch tembak status in_progress
            return ProductionTembakBatch::create([
                'tembak_code' => $tembakCode,
                'wood_material_id' => $woodMaterial->id,
                'wood_weight' => $woodWeight,
                'resin_material_id' => $resinMaterial->id,
                'resin_weight' => $resinWeight,
                'wet_result_weight' => null,
                'residual_resin_weight' => null,
                'residual_resin_material_id' => $resinMaterial->id,
                'dried_result_weight' => null,
                'output_material_id' => null,
                'status' => 'in_progress',
                'tembak_date' => $data['tembak_date'] ?? now(),
                'report_date' => null,
                'pic_name' => $data['pic_name'],
                'report_pic_name' => null,
                'notes' => $data['notes'] ?? null,
                'report_notes' => null,
                'signature_path' => $signaturePath,
            ]);
        });
    }

    /**
     * Submit laporan hasil tembak: Hasil Timbang Basah, Pengembalian Getah Sisa, dan Hasil Kering Masuk Gudang.
     */
    public function submitTembakReport(ProductionTembakBatch $batch, array $data): ProductionTembakBatch
    {
        return DB::transaction(function () use ($batch, $data) {
            if ($batch->isCompleted()) {
                throw new InvalidArgumentException("Sesi tembak {$batch->tembak_code} sudah pernah dilaporkan dan selesai.");
            }

            $wetResultWeight = (float) $data['wet_result_weight'];
            $driedResultWeight = (float) $data['dried_result_weight'];
            $residualResinWeight = isset($data['residual_resin_weight']) ? (float) $data['residual_resin_weight'] : 0.0;

            if ($wetResultWeight <= 0) {
                throw new InvalidArgumentException('Berat hasil timbang basah harus lebih besar dari 0.');
            }

            if ($driedResultWeight <= 0) {
                throw new InvalidArgumentException('Berat hasil kering setelah jemur harus lebih besar dari 0.');
            }

            if ($residualResinWeight < 0) {
                throw new InvalidArgumentException('Getah sisa tembak tidak boleh bernilai negatif.');
            }

            if ($residualResinWeight > (float) $batch->resin_weight) {
                throw new InvalidArgumentException("Getah sisa tembak ({$residualResinWeight} kg) tidak boleh melebihi resin awal yang digunakan ({$batch->resin_weight} kg).");
            }

            // Simpan tanda tangan laporan jika ada
            $reportSignaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_tbk_rep');

            // 1. Pengembalian Getah Sisa ke Stok Gudang jika ada
            $residualResinMatId = $data['residual_resin_material_id'] ?? $batch->residual_resin_material_id ?? $batch->resin_material_id;
            if ($residualResinWeight > 0 && $residualResinMatId) {
                $resinMat = Material::findOrFail($residualResinMatId);
                $resinMat->increment('stock_quantity', $residualResinWeight);
                $resinMat->update([
                    'last_weighed_at' => now(),
                    'last_weighed_by' => $data['report_pic_name'],
                ]);

                MaterialLog::create([
                    'material_id' => $resinMat->id,
                    'type' => 'in',
                    'reference_number' => $batch->tembak_code,
                    'quantity' => $residualResinWeight,
                    'unit' => $resinMat->unit,
                    'actor_by' => $data['report_pic_name'],
                    'source_or_destination' => "Getah Sisa Hasil Tembak #{$batch->tembak_code}",
                    'movement_date' => $data['report_date'] ?? now(),
                    'notes' => "Pengembalian getah sisa hasil tembak {$batch->tembak_code} ke stok gudang.",
                    'signature_path' => $reportSignaturePath,
                ]);
            }

            // 2. Tambahkan Hasil Tembak Kering ke Bahan Gudang Tujuan
            $outputMaterial = Material::findOrFail($data['output_material_id']);
            $outputMaterial->increment('stock_quantity', $driedResultWeight);
            $outputMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['report_pic_name'],
            ]);

            MaterialLog::create([
                'material_id' => $outputMaterial->id,
                'type' => 'in',
                'reference_number' => $batch->tembak_code,
                'quantity' => $driedResultWeight,
                'unit' => $outputMaterial->unit,
                'actor_by' => $data['report_pic_name'],
                'source_or_destination' => "Hasil Tembak Kering #{$batch->tembak_code}",
                'movement_date' => $data['report_date'] ?? now(),
                'notes' => "Penambahan stok bahan hasil tembak kering dari sesi #{$batch->tembak_code}.",
                'signature_path' => $reportSignaturePath,
            ]);

            // 3. Perbarui status sesi tembak menjadi completed
            $batch->update([
                'wet_result_weight' => $wetResultWeight,
                'residual_resin_weight' => $residualResinWeight,
                'residual_resin_material_id' => $residualResinMatId,
                'dried_result_weight' => $driedResultWeight,
                'output_material_id' => $outputMaterial->id,
                'status' => 'completed',
                'report_date' => $data['report_date'] ?? now(),
                'report_pic_name' => $data['report_pic_name'],
                'report_notes' => $data['report_notes'] ?? null,
            ]);

            return $batch->fresh();
        });
    }

    /**
     * Simpan gambar tanda tangan digital berbasis data base64.
     */
    protected function saveSignature(?string $signatureData, string $prefix): ?string
    {
        if (empty($signatureData)) {
            return null;
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
            $data = substr($signatureData, strpos($signatureData, ',') + 1);
            $data = base64_decode($data);
            if ($data !== false) {
                $extension = strtolower($type[1]) === 'jpeg' ? 'jpg' : 'png';
                $fileName = $prefix.'_'.time().'_'.uniqid().'.'.$extension;
                $filePath = 'signatures/'.$fileName;
                Storage::disk('public')->put($filePath, $data);

                return $filePath;
            }
        }

        return null;
    }
}
