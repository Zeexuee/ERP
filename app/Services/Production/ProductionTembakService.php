<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialBranch;
use App\Models\MaterialLog;
use App\Models\ProductionTembakBatch;
use App\Models\ProductionTembakDryingLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ProductionTembakService
{
    private const MAX_SIGNATURE_BYTES = 1000000;

    public function __construct(private MaterialBranchService $branchService) {}

    /**
     * Inisiasi proses tembak dan keluarkan seluruh bahan dari stok gudang.
     *
     * @param  array<string, mixed>  $data
     */
    public function initiateTembakBatch(array $data): ProductionTembakBatch
    {
        $signaturePath = null;

        try {
            return DB::transaction(function () use ($data, &$signaturePath) {
                $woodWeights = $this->aggregateMaterialWeights($data['woods'], 'Berat kayu');
                $resinWeights = $this->aggregateMaterialWeights($data['resins'], 'Berat getah');
                $duplicateMaterialIds = array_intersect(array_keys($woodWeights), array_keys($resinWeights));

                if ($duplicateMaterialIds !== []) {
                    throw new InvalidArgumentException('Barang yang sama tidak boleh dipilih sebagai bahan kayu dan getah sekaligus.');
                }

                $materialIds = array_merge(array_keys($woodWeights), array_keys($resinWeights));
                $materials = Material::query()
                    ->whereKey($materialIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($materials->count() !== count($materialIds)) {
                    throw new InvalidArgumentException('Salah satu barang bahan tembak tidak ditemukan.');
                }

                foreach ($woodWeights + $resinWeights as $materialId => $weight) {
                    $material = $materials->get($materialId);
                    $this->ensureKilogramUnit($material);

                    if ((float) $material->stock_quantity < $weight) {
                        throw new InvalidArgumentException(
                            "Stok {$material->name} tidak mencukupi (tersedia: {$material->stock_quantity} {$material->unit}, dibutuhkan: {$weight} {$material->unit})."
                        );
                    }
                }

                $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_tbk_init');
                $tembakCode = $this->generateTembakCode();

                $batch = ProductionTembakBatch::create([
                    'tembak_code' => $tembakCode,
                    'branch_code' => $this->branchService->generateTembakBranchCode(),
                    'status' => ProductionTembakBatch::STATUS_IN_PROGRESS,
                    'tembak_date' => $data['tembak_date'],
                    'pic_name' => $data['pic_name'],
                    'notes' => $data['notes'] ?? null,
                    'signature_path' => $signaturePath,
                ]);

                foreach ($woodWeights as $materialId => $weight) {
                    $this->consumeMaterial($batch, $materials->get($materialId), $weight, 'wood', $data);
                }

                foreach ($resinWeights as $materialId => $weight) {
                    $this->consumeMaterial($batch, $materials->get($materialId), $weight, 'resin', $data);
                }

                return $batch->fresh(['materials.material']);
            });
        } catch (Throwable $exception) {
            $this->deleteSignature($signaturePath);

            throw $exception;
        }
    }

    /**
     * Simpan laporan hasil tembak dan mulai tahap jemur.
     *
     * @param  array<string, mixed>  $data
     */
    public function submitTembakReport(ProductionTembakBatch $batch, array $data): ProductionTembakBatch
    {
        $reportSignaturePath = null;

        try {
            return DB::transaction(function () use ($batch, $data, &$reportSignaturePath) {
                $lockedBatch = ProductionTembakBatch::query()
                    ->lockForUpdate()
                    ->findOrFail($batch->id);

                if (! $lockedBatch->isInProgress()) {
                    throw new InvalidArgumentException("Laporan hasil tembak {$lockedBatch->tembak_code} sudah pernah diproses.");
                }

                $reportDate = CarbonImmutable::parse($data['report_date'])->startOfDay();
                $tembakDate = CarbonImmutable::parse($lockedBatch->tembak_date)->startOfDay();

                if ($reportDate->lt($tembakDate)) {
                    throw new InvalidArgumentException('Tanggal laporan tidak boleh lebih awal dari tanggal inisiasi tembak.');
                }

                $wetResultWeight = round((float) $data['wet_result_weight'], 2);
                $residualResinWeight = round((float) ($data['residual_resin_weight'] ?? 0), 2);
                $totalResinWeight = (float) $lockedBatch->materials()->where('type', 'resin')->sum('weight');

                if ($residualResinWeight > $totalResinWeight) {
                    throw new InvalidArgumentException(
                        "Getah sisa ({$residualResinWeight} kg) tidak boleh melebihi total getah yang digunakan ({$totalResinWeight} kg)."
                    );
                }

                $reportSignaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_tbk_report');

                if (! $reportSignaturePath) {
                    throw new InvalidArgumentException('Tanda tangan pelapor wajib diisi.');
                }

                $residualMaterial = $this->resolveResidualMaterial(
                    $lockedBatch,
                    $residualResinWeight,
                    $data
                );

                if ($residualMaterial) {
                    $residualUnitCost = $this->calculateResinUnitCost($lockedBatch);
                    $this->addStock($residualMaterial, $residualResinWeight, $residualUnitCost, $data['report_pic_name']);

                    $this->branchService->recordStockIn(
                        $residualMaterial,
                        $lockedBatch->branch_code,
                        $residualResinWeight,
                        MaterialBranch::SOURCE_TEMBAK,
                        $lockedBatch->tembak_code,
                        "Getah sisa hasil tembak {$lockedBatch->tembak_code}."
                    );

                    MaterialLog::create([
                        'material_id' => $residualMaterial->id,
                        'type' => 'in',
                        'reference_number' => $lockedBatch->tembak_code,
                        'quantity' => $residualResinWeight,
                        'unit' => $residualMaterial->unit,
                        'actor_by' => $data['report_pic_name'],
                        'source_or_destination' => "Getah Sisa Hasil Tembak #{$lockedBatch->tembak_code}",
                        'movement_date' => $reportDate,
                        'notes' => "Pengembalian getah sisa hasil tembak {$lockedBatch->tembak_code} ke stok gudang.",
                        'signature_path' => $reportSignaturePath,
                    ]);
                }

                $lockedBatch->update([
                    'wet_result_weight' => $wetResultWeight,
                    'residual_resin_weight' => $residualResinWeight,
                    'residual_resin_material_id' => $residualMaterial?->id,
                    'status' => ProductionTembakBatch::STATUS_DRYING,
                    'report_date' => $reportDate,
                    'report_pic_name' => $data['report_pic_name'],
                    'report_notes' => $data['report_notes'] ?? null,
                    'report_signature_path' => $reportSignaturePath,
                ]);

                return $lockedBatch->fresh([
                    'materials.material',
                    'residualResinMaterial',
                    'dryingLogs',
                ]);
            });
        } catch (Throwable $exception) {
            $this->deleteSignature($reportSignaturePath);

            throw $exception;
        }
    }

    /**
     * Catat satu sesi timbang jemur dan selesaikan batch bila dipilih admin.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordDryingLog(ProductionTembakBatch $batch, array $data): ProductionTembakDryingLog
    {
        return DB::transaction(function () use ($batch, $data) {
            $lockedBatch = ProductionTembakBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->id);

            if (! $lockedBatch->isDrying()) {
                throw new InvalidArgumentException("Sesi {$lockedBatch->tembak_code} tidak sedang berada pada tahap jemur.");
            }

            $latestLog = $lockedBatch->dryingLogs()
                ->reorder()
                ->latest('id')
                ->lockForUpdate()
                ->first();
            $previousWeight = round((float) ($latestLog?->new_weight ?? $lockedBatch->wet_result_weight), 2);
            $newWeight = round((float) $data['new_weight'], 2);
            $weighedDate = CarbonImmutable::parse($data['weighed_date'])->startOfDay();
            $minimumDate = CarbonImmutable::parse($latestLog?->weighed_date ?? $lockedBatch->report_date)->startOfDay();

            if ($newWeight > $previousWeight) {
                throw new InvalidArgumentException("Berat setelah jemur tidak boleh melebihi berat sebelumnya ({$previousWeight} kg).");
            }

            if ($weighedDate->lt($minimumDate)) {
                throw new InvalidArgumentException('Tanggal timbang jemur tidak boleh lebih awal dari laporan jemur sebelumnya.');
            }

            $isFinal = (bool) $data['is_completed'];
            $shrinkageWeight = round($previousWeight - $newWeight, 2);

            $dryingLog = $lockedBatch->dryingLogs()->create([
                'weighed_date' => $weighedDate,
                'previous_weight' => $previousWeight,
                'new_weight' => $newWeight,
                'shrinkage_weight' => $shrinkageWeight,
                'pic_name' => $data['pic_name'],
                'notes' => $data['notes'] ?? null,
                'is_final' => $isFinal,
            ]);

            if ($isFinal) {
                $outputUnitCost = $this->calculateOutputUnitCost($lockedBatch, $newWeight);
                $outputMaterial = $this->resolveOutputMaterial($data, $outputUnitCost, $data['pic_name']);

                $this->addStock($outputMaterial, $newWeight, $outputUnitCost, $data['pic_name']);

                $this->branchService->recordStockIn(
                    $outputMaterial,
                    $lockedBatch->branch_code,
                    $newWeight,
                    MaterialBranch::SOURCE_TEMBAK,
                    $lockedBatch->tembak_code,
                    "Hasil akhir jemur sesi tembak {$lockedBatch->tembak_code}."
                );

                MaterialLog::create([
                    'material_id' => $outputMaterial->id,
                    'type' => 'in',
                    'reference_number' => $lockedBatch->tembak_code,
                    'quantity' => $newWeight,
                    'unit' => $outputMaterial->unit,
                    'actor_by' => $data['pic_name'],
                    'source_or_destination' => "Hasil Jemur Tembak #{$lockedBatch->tembak_code}",
                    'movement_date' => $weighedDate,
                    'notes' => $data['notes'] ?? "Hasil akhir jemur dari sesi {$lockedBatch->tembak_code}.",
                ]);

                $lockedBatch->update([
                    'dried_result_weight' => $newWeight,
                    'output_material_id' => $outputMaterial->id,
                    'status' => ProductionTembakBatch::STATUS_COMPLETED,
                ]);
            }

            return $dryingLog->fresh();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, float>
     */
    private function aggregateMaterialWeights(array $items, string $weightLabel): array
    {
        $weights = [];

        foreach ($items as $item) {
            $materialId = (int) $item['material_id'];
            $weight = round((float) $item['weight'], 2);

            if ($weight <= 0) {
                throw new InvalidArgumentException("{$weightLabel} harus lebih besar dari 0.");
            }

            $weights[$materialId] = round(($weights[$materialId] ?? 0) + $weight, 2);
        }

        return $weights;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function consumeMaterial(
        ProductionTembakBatch $batch,
        Material $material,
        float $weight,
        string $type,
        array $data
    ): void {
        $material->update([
            'stock_quantity' => round((float) $material->stock_quantity - $weight, 2),
            'last_weighed_at' => now(),
            'last_weighed_by' => $data['pic_name'],
        ]);

        // Stok branch pada bahan asal ikut berkurang. Sesi tembak menerbitkan
        // identitas branch barunya sendiri, jadi branch asal tidak dibawa.
        $this->branchService->consumeProportionally($material, $weight);

        $materialLabel = $type === 'wood' ? 'bahan kayu' : 'getah';

        MaterialLog::create([
            'material_id' => $material->id,
            'type' => 'out',
            'reference_number' => $batch->tembak_code,
            'quantity' => $weight,
            'unit' => $material->unit,
            'actor_by' => $data['pic_name'],
            'source_or_destination' => "Inisiasi Tembak #{$batch->tembak_code}",
            'movement_date' => $data['tembak_date'],
            'notes' => $data['notes'] ?? "Pengeluaran {$materialLabel} ({$material->name}) untuk proses tembak.",
            'signature_path' => $batch->signature_path,
        ]);

        $batch->materials()->create([
            'material_id' => $material->id,
            'type' => $type,
            'weight' => $weight,
            'unit_cost' => $material->unit_cost,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveResidualMaterial(
        ProductionTembakBatch $batch,
        float $residualWeight,
        array $data
    ): ?Material {
        $destinationType = $data['residual_destination_type'];

        if ($residualWeight <= 0 || $destinationType === 'none') {
            return null;
        }

        if ($destinationType === 'existing') {
            $material = Material::query()
                ->lockForUpdate()
                ->findOrFail($data['existing_residual_material_id']);

            if (Str::lower(trim($material->category)) !== 'getah') {
                throw new InvalidArgumentException('Barang tujuan getah sisa harus memiliki jenis Getah.');
            }

            $this->ensureKilogramUnit($material);

            return $material;
        }

        if ($destinationType !== 'new') {
            throw new InvalidArgumentException('Pilihan tujuan getah sisa tidak valid.');
        }

        return Material::create([
            'code' => $this->resolveMaterialCode($data['new_residual_code'] ?? null),
            'name' => trim($data['new_residual_name']),
            'category' => 'Getah',
            'unit' => 'kg',
            'stock_quantity' => 0,
            'minimum_stock' => 0,
            'unit_cost' => $this->calculateResinUnitCost($batch),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveOutputMaterial(array $data, float $unitCost, string $picName): Material
    {
        if ($data['destination_type'] === 'existing') {
            $material = Material::query()
                ->lockForUpdate()
                ->findOrFail($data['output_material_id']);

            $this->ensureKilogramUnit($material);

            return $material;
        }

        if ($data['destination_type'] !== 'new') {
            throw new InvalidArgumentException('Pilihan barang baku tujuan tidak valid.');
        }

        return Material::create([
            'code' => $this->resolveMaterialCode($data['new_output_code'] ?? null),
            'name' => trim($data['new_output_name']),
            'category' => 'Bahan Tembak',
            'unit' => 'kg',
            'stock_quantity' => 0,
            'minimum_stock' => 0,
            'unit_cost' => $unitCost,
            'last_weighed_by' => $picName,
        ]);
    }

    private function addStock(Material $material, float $quantity, float $incomingUnitCost, string $picName): void
    {
        $currentQuantity = (float) $material->stock_quantity;
        $newQuantity = round($currentQuantity + $quantity, 2);
        $newUnitCost = $newQuantity > 0
            ? round(
                (($currentQuantity * (float) $material->unit_cost) + ($quantity * $incomingUnitCost)) / $newQuantity,
                2
            )
            : (float) $material->unit_cost;

        $material->update([
            'stock_quantity' => $newQuantity,
            'unit_cost' => $newUnitCost,
            'last_weighed_at' => now(),
            'last_weighed_by' => $picName,
        ]);
    }

    private function calculateResinUnitCost(ProductionTembakBatch $batch): float
    {
        $resinMaterials = $batch->materials()->where('type', 'resin')->get(['weight', 'unit_cost']);
        $totalWeight = (float) $resinMaterials->sum('weight');

        if ($totalWeight <= 0) {
            return 0;
        }

        $totalCost = $resinMaterials->sum(
            fn ($material): float => (float) $material->weight * (float) $material->unit_cost
        );

        return round($totalCost / $totalWeight, 2);
    }

    private function calculateOutputUnitCost(ProductionTembakBatch $batch, float $finalWeight): float
    {
        $materials = $batch->materials()->get(['type', 'weight', 'unit_cost']);
        $totalInputCost = $materials->sum(
            fn ($material): float => (float) $material->weight * (float) $material->unit_cost
        );
        $returnedResinCost = $batch->residual_resin_material_id
            ? (float) $batch->residual_resin_weight * $this->calculateResinUnitCost($batch)
            : 0;

        return round(max($totalInputCost - $returnedResinCost, 0) / $finalWeight, 2);
    }

    private function ensureKilogramUnit(Material $material): void
    {
        if (Str::lower(trim($material->unit)) !== 'kg') {
            throw new InvalidArgumentException("Satuan barang {$material->name} harus kg agar dapat digabungkan dengan hasil proses tembak.");
        }
    }

    private function generateTembakCode(): string
    {
        $prefix = 'TBK-'.now()->format('Ym').'-';
        $latestBatch = ProductionTembakBatch::query()
            ->where('tembak_code', 'like', $prefix.'%')
            ->orderByDesc('tembak_code')
            ->lockForUpdate()
            ->first();
        $sequence = $latestBatch ? (int) Str::afterLast($latestBatch->tembak_code, '-') + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function resolveMaterialCode(?string $requestedCode): string
    {
        if ($requestedCode !== null && trim($requestedCode) !== '') {
            $code = Str::upper(trim($requestedCode));

            if (Material::where('code', $code)->exists()) {
                throw new InvalidArgumentException("Kode barang {$code} sudah digunakan.");
            }

            return $code;
        }

        $latestMaterial = Material::query()->orderByDesc('id')->lockForUpdate()->first();
        $nextId = ($latestMaterial?->id ?? 0) + 1;
        $code = 'MAT-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

        while (Material::where('code', $code)->exists()) {
            $nextId++;
            $code = 'MAT-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    private function saveSignature(?string $signatureData, string $prefix): ?string
    {
        if (! $signatureData) {
            return null;
        }

        if (! preg_match('/^data:image\/(png|jpeg);base64,(.+)$/s', $signatureData, $matches)) {
            throw new InvalidArgumentException('Format tanda tangan tidak valid.');
        }

        $decodedImage = base64_decode($matches[2], true);

        if ($decodedImage === false || mb_strlen($decodedImage, '8bit') > self::MAX_SIGNATURE_BYTES) {
            throw new InvalidArgumentException('Data tanda tangan tidak valid atau terlalu besar.');
        }

        $imageInformation = getimagesizefromstring($decodedImage);
        $allowedMimeTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
        $mimeType = $imageInformation['mime'] ?? null;

        if (! $mimeType || ! isset($allowedMimeTypes[$mimeType])) {
            throw new InvalidArgumentException('Tanda tangan harus berupa gambar PNG atau JPEG yang valid.');
        }

        $filePath = 'signatures/'.$prefix.'_'.Str::uuid().'.'.$allowedMimeTypes[$mimeType];

        if (! Storage::disk('public')->put($filePath, $decodedImage)) {
            throw new InvalidArgumentException('Tanda tangan gagal disimpan.');
        }

        return $filePath;
    }

    private function deleteSignature(?string $signaturePath): void
    {
        if ($signaturePath) {
            Storage::disk('public')->delete($signaturePath);
        }
    }
}
