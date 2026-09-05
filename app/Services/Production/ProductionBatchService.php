<?php

namespace App\Services\Production;

use App\Enums\ProductionRequestStatus;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchMaterial;
use App\Models\ProductionDailyLog;
use App\Models\ProductionRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ProductionBatchService
{
    /**
     * Buat batch produksi baru, alokasikan bahan baku, dan kurangi stok bahan baku dalam DB::transaction().
     */
    public function createBatch(array $validatedData): ProductionBatch
    {
        // 1. Verifikasi ketersediaan stok setiap bahan baku
        foreach ($validatedData['materials'] as $item) {
            $material = Material::find($item['material_id']);
            if (! $material || $material->stock_quantity < $item['quantity_used']) {
                $available = $material ? "{$material->stock_quantity} {$material->unit}" : '0';
                throw new InvalidArgumentException("Stok bahan '{$material?->name}' tidak mencukupi. Tersedia: {$available}, diminta: {$item['quantity_used']} {$material?->unit}.");
            }
        }

        return DB::transaction(function () use ($validatedData) {
            $product = Product::findOrFail($validatedData['product_id']);

            // Penentuan Nomor Batch (Manual / Otomatis)
            if (! empty($validatedData['batch_number'])) {
                $batchNumber = trim($validatedData['batch_number']);
            } else {
                $latestBatch = ProductionBatch::latest('id')->first();
                $maxId = $latestBatch ? $latestBatch->id + 1 : 1;
                $batchNumber = 'BATCH-'.date('Y').'-'.str_pad($maxId, 3, '0', STR_PAD_LEFT);
                while (ProductionBatch::where('batch_number', $batchNumber)->exists()) {
                    $maxId++;
                    $batchNumber = 'BATCH-'.date('Y').'-'.str_pad($maxId, 3, '0', STR_PAD_LEFT);
                }
            }

            $batch = ProductionBatch::create([
                'batch_number' => $batchNumber,
                'product_id' => $product->id,
                'production_request_id' => $validatedData['production_request_id'] ?? null,
                'target_quantity' => $validatedData['target_quantity'],
                'actual_quantity' => null,
                'unit' => $product->unit ?? 'kg',
                'status' => 'in_progress',
                'stage' => 'Sortir',
                'start_date' => $validatedData['start_date'],
                'target_completion_date' => $validatedData['target_completion_date'] ?? null,
                'pic_name' => $validatedData['pic_name'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            // Alokasikan bahan baku & potong stok bahan baku di gudang
            foreach ($validatedData['materials'] as $matItem) {
                $mat = Material::findOrFail($matItem['material_id']);

                ProductionBatchMaterial::create([
                    'production_batch_id' => $batch->id,
                    'material_id' => $mat->id,
                    'quantity_used' => $matItem['quantity_used'],
                    'unit' => $mat->unit,
                ]);

                $mat->decrement('stock_quantity', $matItem['quantity_used']);

                // Catat pemakaian bahan baku ke alur barang gudang (MaterialLog)
                MaterialLog::create([
                    'material_id' => $mat->id,
                    'type' => 'out',
                    'reference_number' => $batch->batch_number,
                    'quantity' => $matItem['quantity_used'],
                    'unit' => $mat->unit,
                    'actor_by' => $batch->pic_name,
                    'source_or_destination' => "Batch #{$batch->batch_number} ({$product->name})",
                    'movement_date' => $batch->start_date,
                    'notes' => "Alokasi pemakaian bahan baku untuk proses produksi batch #{$batch->batch_number}.",
                ]);
            }

            // Catat log harian awal pengerjaan
            ProductionDailyLog::create([
                'production_batch_id' => $batch->id,
                'log_date' => $validatedData['start_date'],
                'stage' => 'Sortir',
                'work_status' => 'selesai',
                'pic_name' => $validatedData['pic_name'],
                'notes' => "Proses produksi batch #{$batch->batch_number} resmi dimulai. Penimbangan dan alokasi bahan baku selesai dipersiapkan.",
            ]);

            // Jika terhubung ke antrean Sales, ubah status antrean menjadi In Production
            if (! empty($validatedData['production_request_id'])) {
                $pr = ProductionRequest::find($validatedData['production_request_id']);
                if ($pr && $pr->status !== ProductionRequestStatus::FINISHED) {
                    $pr->update(['status' => ProductionRequestStatus::IN_PRODUCTION]);
                }
            }

            return $batch;
        });
    }

    /**
     * Catat laporan harian proses produksi (Daily Log).
     */
    public function storeDailyLog(ProductionBatch $batch, array $validatedData, array|UploadedFile|null $attachments = null): ProductionDailyLog
    {
        return DB::transaction(function () use ($batch, $validatedData, $attachments) {
            $attachmentPaths = [];

            if ($attachments instanceof UploadedFile) {
                $attachments = [$attachments];
            }

            if (is_array($attachments)) {
                foreach ($attachments as $file) {
                    if ($file instanceof UploadedFile) {
                        $attachmentPaths[] = $file->store('production-logs/attachments', 'public');
                    }
                }
            }

            $attachmentData = empty($attachmentPaths)
                ? null
                : (count($attachmentPaths) === 1 ? $attachmentPaths[0] : json_encode($attachmentPaths));

            $signaturePath = null;
            if (! empty($validatedData['signature_data'])) {
                $signatureData = $validatedData['signature_data'];
                if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                    $data = substr($signatureData, strpos($signatureData, ',') + 1);
                    $data = base64_decode($data);
                    if ($data !== false) {
                        $extension = strtolower($type[1]) === 'jpeg' ? 'jpg' : 'png';
                        $fileName = 'sig_batch_'.$batch->id.'_'.time().'_'.uniqid().'.'.$extension;
                        $signaturePath = 'production-logs/signatures/'.$fileName;
                        Storage::disk('public')->put($signaturePath, $data);
                    }
                }
            }

            $workStatus = ($validatedData['stage'] === 'Selesai')
                ? 'selesai'
                : ($validatedData['work_status'] ?? 'selesai');

            $log = ProductionDailyLog::create([
                'production_batch_id' => $batch->id,
                'log_date' => $validatedData['log_date'],
                'stage' => $validatedData['stage'],
                'work_status' => $workStatus,
                'pic_name' => $validatedData['pic_name'],
                'notes' => $validatedData['notes'] ?? null,
                'attachment_path' => $attachmentData,
                'signature_path' => $signaturePath,
            ]);

            $batch->update([
                'stage' => $validatedData['stage'],
            ]);

            // Alokasi tambahan bahan baku (misal pada tahap Tembak / Infusi)
            if (! empty($validatedData['materials'])) {
                foreach ($validatedData['materials'] as $matItem) {
                    if (empty($matItem['material_id']) || empty($matItem['quantity_used'])) {
                        continue;
                    }
                    $mat = Material::findOrFail($matItem['material_id']);
                    $qtyUsed = (float) $matItem['quantity_used'];

                    if ($mat->stock_quantity < $qtyUsed) {
                        $available = "{$mat->stock_quantity} {$mat->unit}";
                        throw new InvalidArgumentException("Stok bahan baku '{$mat->name}' tidak mencukupi untuk proses {$validatedData['stage']}. Tersedia: {$available}, diminta: {$qtyUsed} {$mat->unit}.");
                    }

                    ProductionBatchMaterial::create([
                        'production_batch_id' => $batch->id,
                        'material_id' => $mat->id,
                        'quantity_used' => $qtyUsed,
                        'unit' => $mat->unit,
                    ]);

                    $mat->decrement('stock_quantity', $qtyUsed);

                    // Catat pemakaian bahan ke Riwayat Alur Barang Gudang (MaterialLog)
                    MaterialLog::create([
                        'material_id' => $mat->id,
                        'type' => 'out',
                        'reference_number' => $batch->batch_number,
                        'quantity' => $qtyUsed,
                        'unit' => $mat->unit,
                        'actor_by' => $validatedData['pic_name'],
                        'source_or_destination' => "Batch #{$batch->batch_number} ({$batch->product->name}) - Tahap {$validatedData['stage']}",
                        'movement_date' => $validatedData['log_date'],
                        'notes' => "Alokasi pemakaian bahan baku tambahan pada tahap {$validatedData['stage']} batch #{$batch->batch_number}.",
                    ]);
                }
            }

            // Jika admin memilih tahap "Selesai", maka status produksi menjadi completed dan masukkan ke stok barang jadi serta update status pesanan sales
            if ($validatedData['stage'] === 'Selesai' && $batch->status !== 'completed') {
                $actualQty = $validatedData['actual_quantity'] ?? $batch->target_quantity;
                $batch->update([
                    'status' => 'completed',
                    'actual_quantity' => $actualQty,
                    'completed_date' => $validatedData['log_date'],
                ]);

                $product = $batch->product;
                $product->increment('stock_quantity', (int) $actualQty);

                $branchCode = 'PLANT-STL';
                $branchName = 'Pabrik Pengolahan Sentul';

                $branch = ProductBranch::where('product_id', $product->id)
                    ->where('branch_code', $branchCode)
                    ->first();

                if ($branch) {
                    $branch->increment('quantity', (int) $actualQty);
                } else {
                    ProductBranch::create([
                        'product_id' => $product->id,
                        'branch_name' => $branchName,
                        'branch_code' => $branchCode,
                        'quantity' => (int) $actualQty,
                        'specification_notes' => "Hasil proses produksi batch #{$batch->batch_number}.",
                    ]);
                }

                if ($batch->production_request_id && $batch->productionRequest) {
                    $batch->productionRequest->update([
                        'status' => ProductionRequestStatus::FINISHED,
                        'completed_date' => $validatedData['log_date'],
                    ]);
                }
            }

            return $log;
        });
    }

    /**
     * Selesaikan proses produksi batch dan update stok produk jadi dalam DB::transaction().
     */
    public function completeBatch(ProductionBatch $batch, array $validatedData): ProductionBatch
    {
        if ($batch->status === 'completed') {
            throw new InvalidArgumentException('Proses produksi ini sudah diselesaikan sebelumnya.');
        }

        return DB::transaction(function () use ($batch, $validatedData) {
            $batch->update([
                'status' => 'completed',
                'stage' => '5. Selesai & Masuk Stok Barang Jadi',
                'actual_quantity' => $validatedData['actual_quantity'],
                'completed_date' => $validatedData['completed_date'],
            ]);

            $product = $batch->product;
            $product->increment('stock_quantity', (int) $validatedData['actual_quantity']);

            $branchCode = $validatedData['branch_code'] ?? 'PLANT-STL';
            $branchName = $branchCode === 'PLANT-STL' ? 'Pabrik Pengolahan Sentul' : 'Gudang Utama Jakarta';

            $branch = ProductBranch::where('product_id', $product->id)
                ->where('branch_code', $branchCode)
                ->first();

            if ($branch) {
                $branch->increment('quantity', (int) $validatedData['actual_quantity']);
            } else {
                ProductBranch::create([
                    'product_id' => $product->id,
                    'branch_name' => $branchName,
                    'branch_code' => $branchCode,
                    'quantity' => (int) $validatedData['actual_quantity'],
                    'specification_notes' => "Hasil proses produksi batch #{$batch->batch_number}.",
                ]);
            }

            ProductionDailyLog::create([
                'production_batch_id' => $batch->id,
                'log_date' => $validatedData['completed_date'],
                'stage' => '5. Selesai & Masuk Stok Barang Jadi',
                'progress_percentage' => 100,
                'pic_name' => auth()->user()?->name ?? 'Admin Produksi',
                'notes' => "Produksi selesai dan lolos uji Quality Control. Sebanyak {$validatedData['actual_quantity']} {$batch->unit} barang jadi telah dimasukkan ke stok katalog produk ({$branchCode}).".(! empty($validatedData['notes']) ? " Catatan: {$validatedData['notes']}" : ''),
            ]);

            if ($batch->production_request_id && $batch->productionRequest) {
                $batch->productionRequest->update([
                    'status' => ProductionRequestStatus::FINISHED,
                    'completed_date' => $validatedData['completed_date'],
                ]);
            }

            return $batch->refresh();
        });
    }
}
