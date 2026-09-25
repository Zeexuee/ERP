<?php

namespace App\Services\Production;

use App\Enums\FinishingProcessType;
use App\Models\Material;
use App\Models\MaterialBranch;
use App\Models\MaterialLog;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductionFinishingSession;
use App\Models\ProductionFinishingStep;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ProductionFinishingService
{
    private const MAX_SIGNATURE_BYTES = 1000000;

    public function __construct(private MaterialBranchService $branchService) {}

    /**
     * Mulai sesi finishing dan keluarkan bahan hasil tembak dari stok gudang.
     * Identitas branch bahan asal ikut terbawa ke sesi ini.
     *
     * @param  array<string, mixed>  $data
     */
    public function initiateFinishing(array $data): ProductionFinishingSession
    {
        $signaturePath = null;

        try {
            return DB::transaction(function () use ($data, &$signaturePath) {
                $sourceMaterial = Material::query()
                    ->lockForUpdate()
                    ->findOrFail($data['source_material_id']);
                $initialWeight = round((float) $data['initial_weight'], 2);

                $this->ensureKilogramUnit($sourceMaterial);

                if ($initialWeight <= 0) {
                    throw new InvalidArgumentException('Berat bahan yang masuk finishing harus lebih besar dari 0.');
                }

                if ((float) $sourceMaterial->stock_quantity < $initialWeight) {
                    throw new InvalidArgumentException(
                        "Stok {$sourceMaterial->name} tidak mencukupi (tersedia: {$sourceMaterial->stock_quantity} {$sourceMaterial->unit}, dibutuhkan: {$initialWeight} {$sourceMaterial->unit})."
                    );
                }

                $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_fin_init');
                $finishingDate = CarbonImmutable::parse($data['finishing_date'])->startOfDay();

                // Kurangi stok gudang dan ambil identitas branch yang terpakai.
                $sourceMaterial->update([
                    'stock_quantity' => round((float) $sourceMaterial->stock_quantity - $initialWeight, 2),
                    'last_weighed_at' => now(),
                    'last_weighed_by' => $data['pic_name'],
                ]);

                $consumedBranches = $this->branchService->consumeProportionally($sourceMaterial, $initialWeight);
                $carriedBranch = $this->branchService->resolveCarriedBranch($consumedBranches);
                $finishingCode = $this->generateFinishingCode();

                $session = ProductionFinishingSession::create([
                    'finishing_code' => $finishingCode,
                    'branch_code' => $carriedBranch['branch_code'],
                    'branch_is_merged' => $carriedBranch['branch_is_merged'],
                    'source_branch_codes' => $carriedBranch['source_branch_codes'],
                    'source_material_id' => $sourceMaterial->id,
                    'initial_weight' => $initialWeight,
                    'current_weight' => $initialWeight,
                    'status' => ProductionFinishingSession::STATUS_IN_PROGRESS,
                    'finishing_date' => $finishingDate,
                    'pic_name' => $data['pic_name'],
                    'notes' => $data['notes'] ?? null,
                    'signature_path' => $signaturePath,
                ]);

                MaterialLog::create([
                    'material_id' => $sourceMaterial->id,
                    'type' => 'out',
                    'reference_number' => $finishingCode,
                    'quantity' => $initialWeight,
                    'unit' => $sourceMaterial->unit,
                    'actor_by' => $data['pic_name'],
                    'source_or_destination' => "Inisiasi Finishing #{$finishingCode}",
                    'movement_date' => $finishingDate,
                    'notes' => $data['notes'] ?? "Pengeluaran {$sourceMaterial->name} untuk proses finishing {$finishingCode}.",
                    'signature_path' => $signaturePath,
                ]);

                return $session->fresh(['sourceMaterial', 'steps']);
            });
        } catch (Throwable $exception) {
            $this->deleteSignature($signaturePath);

            throw $exception;
        }
    }

    /**
     * Catat satu proses finishing. Urutan prosesnya bebas sesuai kebutuhan
     * produk, dan setiap bahan yang dipakai dicatat beserta harganya.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordStep(ProductionFinishingSession $session, array $data): ProductionFinishingStep
    {
        $signaturePath = null;

        try {
            return DB::transaction(function () use ($session, $data, &$signaturePath) {
                $lockedSession = ProductionFinishingSession::query()
                    ->lockForUpdate()
                    ->findOrFail($session->id);

                if (! $lockedSession->isInProgress()) {
                    throw new InvalidArgumentException("Sesi finishing {$lockedSession->finishing_code} sudah diselesaikan.");
                }

                $processType = FinishingProcessType::from($data['process_type']);
                $weightBefore = round((float) $lockedSession->current_weight, 2);
                $weightAfter = round((float) $data['weight_after'], 2);
                $wasteWeight = round((float) ($data['waste_weight'] ?? 0), 2);
                $stepDate = CarbonImmutable::parse($data['step_date'])->startOfDay();
                $minimumDate = $this->resolveMinimumStepDate($lockedSession);

                if ($weightAfter > $weightBefore) {
                    throw new InvalidArgumentException("Berat setelah proses tidak boleh melebihi berat sebelumnya ({$weightBefore} kg).");
                }

                if ($stepDate->lt($minimumDate)) {
                    throw new InvalidArgumentException('Tanggal proses tidak boleh lebih awal dari proses finishing sebelumnya.');
                }

                $weightLoss = round($weightBefore - $weightAfter, 2);

                if ($wasteWeight > $weightLoss) {
                    throw new InvalidArgumentException(
                        "Ampas buangan ({$wasteWeight} kg) tidak boleh melebihi total susut proses ini ({$weightLoss} kg)."
                    );
                }

                $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_fin_step');
                $sequence = (int) $lockedSession->steps()->reorder()->max('sequence') + 1;

                $step = $lockedSession->steps()->create([
                    'sequence' => $sequence,
                    'process_type' => $processType,
                    'step_date' => $stepDate,
                    'weight_before' => $weightBefore,
                    'weight_after' => $weightAfter,
                    'waste_weight' => $wasteWeight,
                    'material_cost' => 0,
                    'pic_name' => $data['pic_name'],
                    'notes' => $data['notes'] ?? null,
                    'signature_path' => $signaturePath,
                ]);

                $materialCost = $this->consumeStepMaterials($lockedSession, $step, $data, $stepDate);
                $wasteMaterial = $this->resolveWasteMaterial($lockedSession, $processType, $wasteWeight, $data);

                if ($wasteMaterial) {
                    $wasteUnitCost = $this->calculateWasteUnitCost($lockedSession);
                    $this->addStock($wasteMaterial, $wasteWeight, $wasteUnitCost, $data['pic_name']);

                    $this->branchService->recordStockIn(
                        $wasteMaterial,
                        $lockedSession->branch_code,
                        $wasteWeight,
                        MaterialBranch::SOURCE_FINISHING,
                        $lockedSession->finishing_code,
                        "Ampas proses {$processType->label()} dari finishing {$lockedSession->finishing_code}."
                    );

                    MaterialLog::create([
                        'material_id' => $wasteMaterial->id,
                        'type' => 'in',
                        'reference_number' => $lockedSession->finishing_code,
                        'quantity' => $wasteWeight,
                        'unit' => $wasteMaterial->unit,
                        'actor_by' => $data['pic_name'],
                        'source_or_destination' => "Ampas {$processType->label()} Finishing #{$lockedSession->finishing_code}",
                        'movement_date' => $stepDate,
                        'notes' => "Penyimpanan ampas buangan proses {$processType->label()} finishing {$lockedSession->finishing_code}.",
                        'signature_path' => $signaturePath,
                    ]);
                }

                $step->update([
                    'material_cost' => $materialCost,
                    'waste_material_id' => $wasteMaterial?->id,
                ]);

                $lockedSession->update(['current_weight' => $weightAfter]);

                return $step->fresh(['stepMaterials.material', 'wasteMaterial']);
            });
        } catch (Throwable $exception) {
            $this->deleteSignature($signaturePath);

            throw $exception;
        }
    }

    /**
     * Selesaikan finishing atas pernyataan admin dan masukkan hasilnya ke stok
     * barang jadi siap jual dengan identitas branch yang tetap terbawa.
     *
     * @param  array<string, mixed>  $data
     */
    public function completeFinishing(ProductionFinishingSession $session, array $data): ProductionFinishingSession
    {
        $signaturePath = null;

        try {
            return DB::transaction(function () use ($session, $data, &$signaturePath) {
                $lockedSession = ProductionFinishingSession::query()
                    ->lockForUpdate()
                    ->findOrFail($session->id);

                if (! $lockedSession->isInProgress()) {
                    throw new InvalidArgumentException("Sesi finishing {$lockedSession->finishing_code} sudah diselesaikan.");
                }

                if ($lockedSession->steps()->doesntExist()) {
                    throw new InvalidArgumentException('Catat minimal satu proses finishing sebelum menyatakan selesai.');
                }

                $completedDate = CarbonImmutable::parse($data['completed_date'])->startOfDay();
                $minimumDate = $this->resolveMinimumStepDate($lockedSession);

                if ($completedDate->lt($minimumDate)) {
                    throw new InvalidArgumentException('Tanggal selesai tidak boleh lebih awal dari proses finishing terakhir.');
                }

                $outputQuantity = (int) $data['output_quantity'];

                if ($outputQuantity <= 0) {
                    throw new InvalidArgumentException('Jumlah barang jadi harus lebih besar dari 0.');
                }

                $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_fin_done');
                $outputWeight = round((float) $lockedSession->current_weight, 2);
                $product = $this->resolveProduct($data);
                $productBranch = $this->resolveProductBranch($lockedSession, $product, $outputQuantity, $outputWeight);

                $product->increment('stock_quantity', $outputQuantity);

                $lockedSession->update([
                    'status' => ProductionFinishingSession::STATUS_COMPLETED,
                    'output_weight' => $outputWeight,
                    'output_quantity' => $outputQuantity,
                    'product_id' => $product->id,
                    'product_branch_id' => $productBranch->id,
                    'completed_date' => $completedDate,
                    'completion_pic_name' => $data['completion_pic_name'],
                    'completion_notes' => $data['completion_notes'] ?? null,
                    'completion_signature_path' => $signaturePath,
                ]);

                return $lockedSession->fresh([
                    'sourceMaterial',
                    'steps.stepMaterials.material',
                    'steps.wasteMaterial',
                    'product',
                    'productBranch',
                ]);
            });
        } catch (Throwable $exception) {
            $this->deleteSignature($signaturePath);

            throw $exception;
        }
    }

    /**
     * Keluarkan setiap bahan pendukung proses dari gudang dan catat harganya.
     *
     * @param  array<string, mixed>  $data
     */
    private function consumeStepMaterials(
        ProductionFinishingSession $session,
        ProductionFinishingStep $step,
        array $data,
        CarbonImmutable $stepDate
    ): float {
        $requestedMaterials = $data['materials'] ?? [];
        $aggregated = [];

        foreach ($requestedMaterials as $item) {
            if (empty($item['material_id']) || empty($item['quantity'])) {
                continue;
            }

            $materialId = (int) $item['material_id'];
            $quantity = round((float) $item['quantity'], 2);

            if ($quantity <= 0) {
                throw new InvalidArgumentException('Jumlah bahan yang dipakai harus lebih besar dari 0.');
            }

            $aggregated[$materialId] = round(($aggregated[$materialId] ?? 0) + $quantity, 2);
        }

        if ($aggregated === []) {
            return 0.0;
        }

        $materials = Material::query()
            ->whereKey(array_keys($aggregated))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($materials->count() !== count($aggregated)) {
            throw new InvalidArgumentException('Salah satu bahan proses finishing tidak ditemukan.');
        }

        $totalCost = 0.0;
        $processLabel = $step->process_type->label();

        foreach ($aggregated as $materialId => $quantity) {
            $material = $materials->get($materialId);

            if ($material->id === $session->source_material_id) {
                throw new InvalidArgumentException('Bahan pendukung tidak boleh sama dengan bahan utama yang sedang difinishing.');
            }

            if ((float) $material->stock_quantity < $quantity) {
                throw new InvalidArgumentException(
                    "Stok {$material->name} tidak mencukupi (tersedia: {$material->stock_quantity} {$material->unit}, dibutuhkan: {$quantity} {$material->unit})."
                );
            }

            $material->update([
                'stock_quantity' => round((float) $material->stock_quantity - $quantity, 2),
                'last_weighed_at' => now(),
                'last_weighed_by' => $data['pic_name'],
            ]);

            $this->branchService->consumeProportionally($material, $quantity);

            $step->stepMaterials()->create([
                'material_id' => $material->id,
                'quantity' => $quantity,
                'unit' => $material->unit,
                'unit_cost' => $material->unit_cost,
            ]);

            MaterialLog::create([
                'material_id' => $material->id,
                'type' => 'out',
                'reference_number' => $session->finishing_code,
                'quantity' => $quantity,
                'unit' => $material->unit,
                'actor_by' => $data['pic_name'],
                'source_or_destination' => "Proses {$processLabel} Finishing #{$session->finishing_code}",
                'movement_date' => $stepDate,
                'notes' => "Pemakaian {$material->name} pada proses {$processLabel} finishing {$session->finishing_code}.",
                'signature_path' => $step->signature_path,
            ]);

            $totalCost = round($totalCost + ($quantity * (float) $material->unit_cost), 2);
        }

        return $totalCost;
    }

    /**
     * Tentukan barang gudang tujuan ampas buangan bila admin memilih menyimpannya.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveWasteMaterial(
        ProductionFinishingSession $session,
        FinishingProcessType $processType,
        float $wasteWeight,
        array $data
    ): ?Material {
        $destinationType = $data['waste_destination_type'] ?? 'none';

        if ($wasteWeight <= 0 || $destinationType === 'none') {
            return null;
        }

        if ($destinationType === 'existing') {
            $material = Material::query()
                ->lockForUpdate()
                ->findOrFail($data['waste_material_id']);

            $this->ensureKilogramUnit($material);

            return $material;
        }

        if ($destinationType !== 'new') {
            throw new InvalidArgumentException('Pilihan tujuan ampas buangan tidak valid.');
        }

        return Material::create([
            'code' => $this->resolveMaterialCode($data['new_waste_code'] ?? null),
            'name' => trim($data['new_waste_name']),
            'category' => 'Ampas Finishing',
            'unit' => 'kg',
            'stock_quantity' => 0,
            'minimum_stock' => 0,
            'unit_cost' => $this->calculateWasteUnitCost($session),
            'last_weighed_by' => $data['pic_name'],
        ]);
    }

    /**
     * Tentukan barang jadi tujuan hasil finishing.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveProduct(array $data): Product
    {
        if ($data['product_destination_type'] === 'existing') {
            return Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);
        }

        if ($data['product_destination_type'] !== 'new') {
            throw new InvalidArgumentException('Pilihan barang jadi tujuan tidak valid.');
        }

        return Product::create([
            'sku' => $this->resolveProductSku($data['new_product_sku'] ?? null),
            'name' => trim($data['new_product_name']),
            'price' => round((float) ($data['new_product_price'] ?? 0), 2),
            'stock_quantity' => 0,
            'unit' => 'kg',
        ]);
    }

    /**
     * Simpan hasil finishing pada branch barang jadi sesuai identitas produksinya
     * sehingga jejak "produk jadi ... branch ..." tetap terbaca.
     */
    private function resolveProductBranch(
        ProductionFinishingSession $session,
        Product $product,
        int $outputQuantity,
        float $outputWeight
    ): ProductBranch {
        $branchCode = $session->branch_code ?? $session->finishing_code;
        $specification = sprintf(
            'Hasil finishing %s (branch %s). Berat akhir %s kg dari bahan %s.',
            $session->finishing_code,
            $session->branch_label,
            number_format($outputWeight, 2, ',', '.'),
            $session->sourceMaterial?->name ?? '-'
        );

        $branch = ProductBranch::query()
            ->where('product_id', $product->id)
            ->where('branch_code', $branchCode)
            ->lockForUpdate()
            ->first();

        if ($branch) {
            $branch->update([
                'quantity' => (int) $branch->quantity + $outputQuantity,
                'specification_notes' => $specification,
            ]);

            return $branch;
        }

        return ProductBranch::create([
            'product_id' => $product->id,
            'branch_name' => "Branch Produksi {$session->branch_label}",
            'branch_code' => $branchCode,
            'quantity' => $outputQuantity,
            'specification_notes' => $specification,
        ]);
    }

    private function resolveMinimumStepDate(ProductionFinishingSession $session): CarbonImmutable
    {
        $latestStepDate = $session->steps()->reorder()->max('step_date');

        return CarbonImmutable::parse($latestStepDate ?? $session->finishing_date)->startOfDay();
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

    /**
     * Harga satuan ampas mengikuti harga bahan utama yang sedang difinishing.
     */
    private function calculateWasteUnitCost(ProductionFinishingSession $session): float
    {
        return round((float) ($session->sourceMaterial?->unit_cost ?? 0), 2);
    }

    private function ensureKilogramUnit(Material $material): void
    {
        if (Str::lower(trim($material->unit)) !== 'kg') {
            throw new InvalidArgumentException("Satuan barang {$material->name} harus kg agar dapat diproses finishing.");
        }
    }

    private function generateFinishingCode(): string
    {
        $prefix = 'FIN-'.now()->format('Ym').'-';
        $latestSession = ProductionFinishingSession::query()
            ->where('finishing_code', 'like', $prefix.'%')
            ->orderByDesc('finishing_code')
            ->lockForUpdate()
            ->first();
        $sequence = $latestSession ? (int) Str::afterLast($latestSession->finishing_code, '-') + 1 : 1;

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

    private function resolveProductSku(?string $requestedSku): string
    {
        if ($requestedSku !== null && trim($requestedSku) !== '') {
            $sku = Str::upper(trim($requestedSku));

            if (Product::where('sku', $sku)->exists()) {
                throw new InvalidArgumentException("SKU produk {$sku} sudah digunakan.");
            }

            return $sku;
        }

        $latestProduct = Product::query()->orderByDesc('id')->lockForUpdate()->first();
        $nextId = ($latestProduct?->id ?? 0) + 1;
        $sku = 'PRD-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

        while (Product::where('sku', $sku)->exists()) {
            $nextId++;
            $sku = 'PRD-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
        }

        return $sku;
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
