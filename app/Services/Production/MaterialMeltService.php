<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\MaterialMeltBatch;
use App\Models\MaterialMeltLog;
use Illuminate\Support\Facades\DB;

class MaterialMeltService
{
    /**
     * Inisiasi proses pencairan baru.
     * Mengurangi stok getah padat dari Gudang Utama.
     */
    public function initiateMeltBatch(array $data): MaterialMeltBatch
    {
        return DB::transaction(function () use ($data) {
            $sourceMaterial = Material::findOrFail($data['source_material_id']);

            if ($sourceMaterial->stock_quantity < $data['initial_weight']) {
                throw new \InvalidArgumentException("Stok {$sourceMaterial->name} tidak mencukupi. Tersedia: {$sourceMaterial->stock_quantity} {$sourceMaterial->unit}");
            }

            $meltCode = $this->generateMeltCode();

            // Kurangi stok bahan mentah (Getah Padat)
            $sourceMaterial->decrement('stock_quantity', $data['initial_weight']);
            $sourceMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['pic_name'] ?? 'Sistem',
            ]);

            // Catat log alur keluar bahan mentah
            MaterialLog::create([
                'material_id' => $sourceMaterial->id,
                'type' => 'out',
                'reference_number' => $meltCode,
                'quantity' => $data['initial_weight'],
                'unit' => $sourceMaterial->unit,
                'actor_by' => $data['pic_name'] ?? 'Sistem',
                'source_or_destination' => 'Inisiasi Pencairan Getah',
                'movement_date' => $data['melt_date'] ?? now(),
                'notes' => $data['notes'] ?? 'Pengambilan untuk inisiasi proses pencairan.',
            ]);

            // Buat batch pencairan
            $batch = MaterialMeltBatch::create([
                'melt_code' => $meltCode,
                'source_material_id' => $data['source_material_id'],
                'initial_weight' => $data['initial_weight'],
                'status' => 'in_progress',
                'melt_date' => $data['melt_date'],
                'pic_name' => $data['pic_name'],
                'notes' => $data['notes'] ?? null,
            ]);

            return $batch;
        });
    }

    /**
     * Pelaporan hasil pencairan (Mencatat cairan yang dihasilkan pertama kali).
     */
    public function submitMeltReport(MaterialMeltBatch $batch, array $data): MaterialMeltBatch
    {
        if ($batch->status !== 'in_progress') {
            throw new \InvalidArgumentException('Batch pencairan ini tidak dalam status proses awal.');
        }

        return DB::transaction(function () use ($batch, $data) {
            $liquidWeight = (float) $data['initial_liquid_weight'];

            if ($liquidWeight <= 0) {
                throw new \InvalidArgumentException('Berat cairan hasil pencairan harus lebih besar dari 0.');
            }

            // Buat material baru untuk cairan hasil pencairan ini
            $targetMaterial = Material::create([
                'code' => $batch->melt_code,
                'name' => $data['target_material_name'],
                'category' => 'Getah Cair',
                'unit' => 'kg',
                'stock_quantity' => $liquidWeight,
                'minimum_stock' => 0,
                'unit_cost' => $batch->sourceMaterial->unit_cost ?? 0,
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['pic_name'] ?? 'Sistem',
            ]);

            // Catat log penambahan stok cair ke Gudang
            MaterialLog::create([
                'material_id' => $targetMaterial->id,
                'type' => 'in',
                'reference_number' => $batch->melt_code,
                'quantity' => $liquidWeight,
                'unit' => $targetMaterial->unit,
                'actor_by' => $data['pic_name'] ?? 'Sistem',
                'source_or_destination' => 'Hasil Pencairan Getah',
                'movement_date' => $data['report_date'] ?? now(),
                'notes' => 'Pemasukan cairan awal dari proses pencairan. '.($data['notes'] ?? ''),
            ]);

            // Perbarui batch
            $batch->update([
                'status' => 'monitoring',
                'target_material_id' => $targetMaterial->id,
                'initial_liquid_weight' => $liquidWeight,
                'current_weight' => $liquidWeight,
                'report_date' => $data['report_date'] ?? now(),
                'report_pic_name' => $data['pic_name'] ?? 'Sistem',
            ]);

            return $batch;
        });
    }

    /**
     * Catat hasil timbang ulang harian (Penguapan).
     */
    public function logEvaporation(MaterialMeltBatch $batch, array $data): MaterialMeltLog
    {
        if ($batch->status !== 'monitoring') {
            throw new \InvalidArgumentException('Batch pencairan ini tidak sedang dipantau.');
        }

        return DB::transaction(function () use ($batch, $data) {
            $previousWeight = $batch->current_weight;
            $newWeight = $data['new_weight'];

            if ($newWeight > $previousWeight) {
                throw new \InvalidArgumentException("Berat baru tidak boleh lebih besar dari berat sebelumnya ({$previousWeight}).");
            }

            $evaporated = $previousWeight - $newWeight;

            // Catat log timbang ulang
            $log = $batch->logs()->create([
                'weighed_date' => $data['weighed_date'],
                'previous_weight' => $previousWeight,
                'new_weight' => $newWeight,
                'evaporated_weight' => $evaporated,
                'pic_name' => $data['pic_name'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Update berat cair saat ini di batch agar sinkron dengan gudang
            $batch->update([
                'current_weight' => $newWeight,
            ]);

            // Update stok cairan di gudang (karena menyusut)
            if ($batch->target_material_id && $evaporated > 0) {
                $targetMaterial = $batch->targetMaterial;
                $targetMaterial->decrement('stock_quantity', $evaporated);

                MaterialLog::create([
                    'material_id' => $targetMaterial->id,
                    'type' => 'out',
                    'reference_number' => $batch->melt_code,
                    'quantity' => $evaporated,
                    'unit' => $targetMaterial->unit,
                    'actor_by' => $data['pic_name'] ?? 'Sistem',
                    'source_or_destination' => 'Penguapan Pencairan (Susut)',
                    'movement_date' => $data['weighed_date'],
                    'notes' => 'Penyusutan cairan (penguapan).',
                ]);
            }

            return $log;
        });
    }

    /**
     * Selesaikan proses pemantauan.
     */
    public function completeMeltBatch(MaterialMeltBatch $batch, array $data): void
    {
        if ($batch->status !== 'monitoring') {
            throw new \InvalidArgumentException('Batch pencairan ini sudah berstatus selesai.');
        }

        DB::transaction(function () use ($batch) {
            // Update status batch menjadi selesai
            $batch->update([
                'status' => 'completed',
            ]);
        });
    }

    /**
     * Hasilkan kode unik pencairan MLT-YYYYMMDD-XXXX
     */
    private function generateMeltCode(): string
    {
        $datePrefix = 'MLT-'.date('Ymd').'-';
        $latest = MaterialMeltBatch::where('melt_code', 'like', $datePrefix.'%')
            ->orderBy('melt_code', 'desc')
            ->first();

        if (! $latest) {
            return $datePrefix.'0001';
        }

        $sequence = (int) substr($latest->melt_code, -4);
        $nextSequence = str_pad((string) ($sequence + 1), 4, '0', STR_PAD_LEFT);

        return $datePrefix.$nextSequence;
    }
}
