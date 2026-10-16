<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialBranch;
use App\Models\ProductionColoringBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ProductionColoringService
{
    private const MAX_SIGNATURE_BYTES = 1000000;

    /**
     * Start a new coloring batch from a material branch.
     */
    public function startColoringBatch(array $data): ProductionColoringBatch
    {
        $signaturePath = null;
        try {
            return DB::transaction(function () use ($data, &$signaturePath) {
                $branch = MaterialBranch::findOrFail($data['material_branch_id']);

                $batchCode = $branch->branch_code;
                $totalWeight = $branch->quantity;

                $branch->quantity = 0;
                $branch->save();

                $material = $branch->material;
                $material->stock_quantity -= $totalWeight;
                $material->save();

                $signaturePath = $this->saveSignature($data['signature_data'] ?? null, 'sig_clr_init');

                $batch = ProductionColoringBatch::create([
                    'batch_code' => $batchCode,
                    'process_type' => $data['process_type'],
                    'coloring_date' => $data['coloring_date'],
                    'pic_name' => $data['pic_name'],
                    'notes' => $data['notes'] ?? null,
                    'material_branch_id' => $branch->id,
                    'total_weight' => $totalWeight,
                    'status' => 'in_progress',
                    'signature_path' => $signaturePath,
                ]);

                // Handle multiple support materials
                if (! empty($data['materials'])) {
                    foreach ($data['materials'] as $item) {
                        $supportMat = Material::findOrFail($item['material_id']);
                        $weight = round((float) $item['weight'], 2);

                        if ($weight > 0) {
                            $supportMat->stock_quantity -= $weight;
                            $supportMat->save();

                            $batch->materials()->create([
                                'material_id' => $supportMat->id,
                                'weight' => $weight,
                            ]);
                        }
                    }
                }

                return $batch;
            });
        } catch (Throwable $e) {
            $this->deleteSignature($signaturePath);
            throw $e;
        }
    }

    public function reportColoringBatch(ProductionColoringBatch $batch, array $data): ProductionColoringBatch
    {
        $reportSignaturePath = null;
        $reportImagePath = null;
        try {
            return DB::transaction(function () use ($batch, $data, &$reportSignaturePath, &$reportImagePath) {
                $reportSignaturePath = $this->saveSignature($data['report_signature_data'] ?? null, 'sig_clr_report');

                if (isset($data['report_image']) && $data['report_image'] instanceof UploadedFile) {
                    $reportImagePath = $data['report_image']->store('colorings', 'public');
                }

                $status = $data['continuation_action'] === 'hold' ? 'waiting_continuation' : 'completed';

                $batch->update([
                    'result_weight' => $data['result_weight'] ?? $batch->result_weight,
                    'loss_weight' => $data['loss_weight'] ?? $batch->loss_weight,
                    'report_date' => $data['report_date'],
                    'report_pic_name' => $data['report_pic_name'],
                    'report_notes' => $data['report_notes'] ?? null,
                    'status' => $status,
                    'report_signature_path' => $reportSignaturePath,
                    'report_image_path' => $reportImagePath,
                ]);

                if ($status === 'completed' && $data['result_weight'] > 0) {
                    $note = 'Hasil selesai dari proses '.$batch->process_type;
                    if ($data['continuation_action'] === 'continue_celup') {
                        $note = 'Menunggu proses Celup (Hasil dari '.$batch->process_type.')';
                    } elseif ($data['continuation_action'] === 'continue_warna') {
                        $note = 'Menunggu proses Warna (Hasil dari '.$batch->process_type.')';
                    }

                    // Create output category if not exists
                    $finishingMaterial = Material::firstOrCreate(
                        ['category' => 'Bahan Finishing', 'name' => 'Hasil Celup/Warna'],
                        [
                            'code' => 'FIN-001',
                            'unit' => 'kg',
                            'stock_quantity' => 0,
                            'minimum_stock' => 0,
                            'unit_cost' => 0,
                        ]
                    );

                    $outputBranch = MaterialBranch::create([
                        'material_id' => $finishingMaterial->id,
                        'branch_code' => $batch->batch_code, // Kode branch output = kode branch bahan baku
                        'quantity' => $data['result_weight'],
                        'source_type' => 'finishing',
                        'source_reference' => $batch->batch_code,
                        'notes' => $note,
                    ]);

                    $finishingMaterial->stock_quantity += $data['result_weight'];
                    $finishingMaterial->save();

                    // If continuing to next process, immediately initiate it
                    if (in_array($data['continuation_action'], ['continue_celup', 'continue_warna'])) {
                        $nextProcessType = $data['continuation_action'] === 'continue_celup' ? 'celup' : 'warna';

                        // Deduct from branch and material as it enters new process immediately
                        $outputBranch->quantity = 0;
                        $outputBranch->save();

                        $finishingMaterial->stock_quantity -= $data['result_weight'];
                        $finishingMaterial->save();

                        $newBatch = ProductionColoringBatch::create([
                            'batch_code' => $outputBranch->branch_code,
                            'process_type' => $nextProcessType,
                            'coloring_date' => $data['report_date'],
                            'pic_name' => $data['report_pic_name'],
                            'notes' => 'Lanjutan otomatis dari proses '.$batch->process_type,
                            'material_branch_id' => $outputBranch->id,
                            'total_weight' => $data['result_weight'],
                            'status' => 'in_progress',
                            'signature_path' => $reportSignaturePath, // reuse report signature for new initiation
                        ]);

                        // Handle multiple support materials for the new process
                        if (! empty($data['materials'])) {
                            foreach ($data['materials'] as $item) {
                                $supportMat = Material::findOrFail($item['material_id']);
                                $weight = round((float) $item['weight'], 2);

                                if ($weight > 0) {
                                    $supportMat->stock_quantity -= $weight;
                                    $supportMat->save();

                                    $newBatch->materials()->create([
                                        'material_id' => $supportMat->id,
                                        'weight' => $weight,
                                    ]);
                                }
                            }
                        }
                    }
                }

                return $batch;
            });
        } catch (Throwable $e) {
            $this->deleteSignature($reportSignaturePath);
            if ($reportImagePath) {
                Storage::disk('public')->delete($reportImagePath);
            }
            throw $e;
        }
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
