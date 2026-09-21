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
            // Validasi Stok Kayu & Resin terlebih dahulu
            $woods = [];
            foreach ($data['woods'] as $wood) {
                $material = Material::findOrFail($wood['material_id']);
                $weight = (float) $wood['weight'];
                if ($weight <= 0) {
                    throw new InvalidArgumentException('Berat kayu awal harus lebih besar dari 0.');
                }
                if ($material->stock_quantity < $weight) {
                    throw new InvalidArgumentException(
                        "Stok kayu {$material->name} tidak mencukupi (Tersedia: {$material->stock_quantity} {$material->unit}, Dibutuhkan: {$weight} {$material->unit})."
                    );
                }
                $woods[] = ['model' => $material, 'weight' => $weight];
            }

            $resins = [];
            foreach ($data['resins'] as $resin) {
                $material = Material::findOrFail($resin['material_id']);
                $weight = (float) $resin['weight'];
                if ($weight <= 0) {
                    throw new InvalidArgumentException('Kuantitas minyak/resin harus lebih besar dari 0.');
                }
                if ($material->stock_quantity < $weight) {
                    throw new InvalidArgumentException(
                        "Stok resin {$material->name} tidak mencukupi (Tersedia: {$material->stock_quantity} {$material->unit}, Dibutuhkan: {$weight} {$material->unit})."
                    );
                }
                $resins[] = ['model' => $material, 'weight' => $weight];
            }

            // Simpan tanda tangan digital jika ada
            $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_tbk_init');

            // Generate Kode Tembak TBK-YYYYMM-XXXX
            $latest = ProductionTembakBatch::latest('id')->first();
            $tembakCode = 'TBK-'.date('Ym').'-'.str_pad(($latest ? $latest->id + 1 : 1), 4, '0', STR_PAD_LEFT);

            // Buat record batch tembak status in_progress
            $batch = ProductionTembakBatch::create([
                'tembak_code' => $tembakCode,
                'wet_result_weight' => null,
                'residual_resin_weight' => null,
                'residual_resin_material_id' => null,
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

            // Proses potongan stok kayu & insert tabel pivot
            foreach ($woods as $woodData) {
                $woodMaterial = $woodData['model'];
                $woodWeight = $woodData['weight'];

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

                $batch->materials()->create([
                    'material_id' => $woodMaterial->id,
                    'type' => 'wood',
                    'weight' => $woodWeight,
                    'unit_cost' => $woodMaterial->unit_cost,
                ]);
            }

            // Proses potongan stok resin & insert tabel pivot
            foreach ($resins as $resinData) {
                $resinMaterial = $resinData['model'];
                $resinWeight = $resinData['weight'];

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

                $batch->materials()->create([
                    'material_id' => $resinMaterial->id,
                    'type' => 'resin',
                    'weight' => $resinWeight,
                    'unit_cost' => $resinMaterial->unit_cost,
                ]);
            }

            return $batch;
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

            $totalResinWeight = $batch->materials()->where('type', 'resin')->sum('weight');

            if ($residualResinWeight > (float) $totalResinWeight) {
                throw new InvalidArgumentException("Getah sisa tembak ({$residualResinWeight} kg) tidak boleh melebihi total resin awal yang digunakan ({$totalResinWeight} kg).");
            }

            // Simpan tanda tangan laporan jika ada
            $reportSignaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_tbk_rep');

            // 1. Pengembalian Getah Sisa ke Stok Gudang jika ada
            // Ambil dari input, jika null maka gunakan material id resin pertama (sebagai default) atau yang ada di input
            $defaultResinMatId = $batch->materials()->where('type', 'resin')->first()->material_id ?? null;
            $residualResinMatId = $data['residual_resin_material_id'] ?? $defaultResinMatId;
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
