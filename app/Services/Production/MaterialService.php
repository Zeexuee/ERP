<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\MaterialReceipt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaterialService
{
    /**
     * Catat barang masuk, auto-create material jika baru, dan perbarui stok dalam DB::transaction().
     */
    public function recordReceipt(array $validatedData, ?UploadedFile $imageFile = null): MaterialReceipt
    {
        return DB::transaction(function () use ($validatedData, $imageFile) {
            $materialName = trim($validatedData['material_name']);

            if (! empty($validatedData['material_id'])) {
                $material = Material::find($validatedData['material_id']);
            } else {
                $material = Material::where('name', 'like', $materialName)->first();
            }

            if (! $material) {
                $unit = strtolower(trim($validatedData['unit'] ?? 'kg'));
                $category = trim($validatedData['category'] ?? '') ?: 'Bahan Baku';

                $maxId = (int) (Material::max('id') ?? 0);
                $materialCode = 'MAT-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
                while (Material::where('code', $materialCode)->exists()) {
                    $maxId++;
                    $materialCode = 'MAT-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
                }

                $material = Material::create([
                    'code' => $materialCode,
                    'name' => $materialName,
                    'category' => $category,
                    'unit' => $unit,
                    'stock_quantity' => 0,
                    'minimum_stock' => 0,
                    'unit_cost' => $validatedData['unit_cost'] ?? 0,
                ]);
            } else {
                $unit = $material->unit;
            }

            $imagePath = null;
            if ($imageFile) {
                $imagePath = $imageFile->store('material-receipts/images', 'public');
            }

            $signaturePath = null;
            if (! empty($validatedData['signature_data']) && str_contains($validatedData['signature_data'], 'base64,')) {
                $base64Data = explode('base64,', $validatedData['signature_data'])[1] ?? null;
                if ($base64Data) {
                    $decodedImage = base64_decode($base64Data);
                    $signatureFilename = 'material-receipts/signatures/sig_'.time().'_'.uniqid().'.png';
                    Storage::disk('public')->put($signatureFilename, $decodedImage);
                    $signaturePath = $signatureFilename;
                }
            }

            $latestReceipt = MaterialReceipt::latest('id')->first();
            $receiptNumber = 'IN-'.date('Y').'-'.str_pad(($latestReceipt ? $latestReceipt->id + 1 : 1), 4, '0', STR_PAD_LEFT);

            $receipt = MaterialReceipt::create([
                'receipt_number' => $receiptNumber,
                'material_id' => $material->id,
                'quantity' => $validatedData['quantity'],
                'ordered_quantity' => $validatedData['ordered_quantity'] ?? null,
                'unit' => $unit,
                'unit_cost' => $validatedData['unit_cost'] ?? $material->unit_cost,
                'source_or_supplier' => $validatedData['source_or_supplier'],
                'received_date' => $validatedData['received_date'],
                'received_by' => $validatedData['received_by'] ?? auth()->user()?->name ?? 'Sistem',
                'notes' => $validatedData['notes'] ?? null,
                'signature_path' => $signaturePath,
                'image_path' => $imagePath,
            ]);

            $material->increment('stock_quantity', $validatedData['quantity']);
            $material->update(['last_weighed_at' => now()]);

            if (! empty($validatedData['unit_cost']) && $validatedData['unit_cost'] > 0) {
                $material->update(['unit_cost' => $validatedData['unit_cost']]);
            }

            // Catat ke alur barang gudang (MaterialLog)
            MaterialLog::create([
                'material_id' => $material->id,
                'type' => 'in',
                'reference_number' => $receiptNumber,
                'quantity' => $validatedData['quantity'],
                'unit' => $unit,
                'actor_by' => $validatedData['received_by'] ?? auth()->user()?->name ?? 'Sistem',
                'source_or_destination' => $validatedData['source_or_supplier'],
                'movement_date' => $validatedData['received_date'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            self::pruneOldLogs(60);

            return $receipt;
        });
    }

    /**
     * Hitung ulang stok fisik (Opname / Timbang Ulang) dalam DB::transaction().
     */
    public function recountStock(Material $material, float $actualStock, ?string $weighedBy = null, ?string $notes = null): Material
    {
        $weighedBy = $weighedBy ?? 'Sistem';

        return DB::transaction(function () use ($material, $actualStock, $weighedBy, $notes) {
            $material->update([
                'stock_quantity' => $actualStock,
                'last_weighed_at' => now(),
                'last_weighed_by' => $weighedBy,
            ]);

            $refNumber = 'OPN-'.date('Ymd').'-'.str_pad($material->id, 4, '0', STR_PAD_LEFT);
            MaterialLog::create([
                'material_id' => $material->id,
                'type' => 'recount',
                'reference_number' => $refNumber,
                'quantity' => $actualStock,
                'unit' => $material->unit,
                'actor_by' => ! empty($weighedBy) ? $weighedBy . ' (Warehouse)' : 'Warehouse',
                'source_or_destination' => 'Timbang Ulang Stok Opname',
                'movement_date' => now(),
                'notes' => $notes ?: null,
            ]);

            self::pruneOldLogs(60);

            return $material->refresh();
        });
    }

    /**
     * Tambah master bahan baku baru dalam DB::transaction().
     */
    public function storeMaterial(array $data): Material
    {
        return DB::transaction(function () use ($data) {
            $code = ! empty($data['code']) ? strtoupper(trim($data['code'])) : null;
            if (! $code) {
                $maxId = (int) (Material::max('id') ?? 0);
                $code = 'MAT-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
                while (Material::where('code', $code)->exists()) {
                    $maxId++;
                    $code = 'MAT-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
                }
            }

            $stockQuantity = isset($data['stock_quantity']) && $data['stock_quantity'] !== '' ? (float) $data['stock_quantity'] : 0.0;

            return Material::create([
                'code' => $code,
                'name' => trim($data['name']),
                'category' => ! empty($data['category']) ? trim($data['category']) : 'Kayu Tembak',
                'unit' => ! empty($data['unit']) ? strtolower(trim($data['unit'])) : 'kg',
                'stock_quantity' => $stockQuantity,
                'minimum_stock' => isset($data['minimum_stock']) && $data['minimum_stock'] !== '' ? (float) $data['minimum_stock'] : 0.0,
                'unit_cost' => isset($data['unit_cost']) && $data['unit_cost'] !== '' ? (float) $data['unit_cost'] : 0.0,
                'last_weighed_at' => $stockQuantity > 0 ? now() : null,
            ]);
        });
    }

    /**
     * Perbarui data bahan baku (Nama, Kategori, Minimal Alert).
     */
    public function updateMaterial(Material $material, array $data): Material
    {
        return DB::transaction(function () use ($material, $data) {
            $material->update([
                'code' => strtoupper(trim($data['code'])),
                'name' => $data['name'],
                'category' => $data['category'],
                'minimum_stock' => $data['minimum_stock'],
            ]);

            return $material->refresh();
        });
    }

    /**
     * Split bahan baku menjadi bahan baku baru atau digabung ke bahan baku yang ada dalam DB::transaction().
     */
    public function sortMaterial(Material $sourceMaterial, array $data): array
    {
        $sortedQty = (float) $data['sorted_quantity'];

        if ($sortedQty > (float) $sourceMaterial->stock_quantity) {
            throw new \InvalidArgumentException("Kuantitas split ({$sortedQty} {$sourceMaterial->unit}) melebihi stok yang tersedia ({$sourceMaterial->stock_quantity} {$sourceMaterial->unit}).");
        }

        return DB::transaction(function () use ($sourceMaterial, $data, $sortedQty) {
            $destinationType = $data['destination_type'];
            $actorBy = $data['actor_by'] ?? 'Sistem';
            $notes = $data['notes'] ?? null;

            if ($destinationType === 'existing') {
                $targetMaterial = Material::findOrFail($data['existing_material_id']);

                if ($sourceMaterial->id === $targetMaterial->id) {
                    throw new \InvalidArgumentException('Bahan baku tujuan split tidak boleh sama dengan bahan baku asal.');
                }

                if (strtolower(trim($sourceMaterial->unit)) !== strtolower(trim($targetMaterial->unit))) {
                    throw new \InvalidArgumentException("Unit satuan bahan baku yang digabungkan harus sama (Asal: {$sourceMaterial->unit}, Target: {$targetMaterial->unit}).");
                }
            } else {
                $newCode = ! empty($data['new_code']) ? strtoupper(trim($data['new_code'])) : null;
                if (! $newCode) {
                    $maxId = (int) (Material::max('id') ?? 0);
                    $newCode = 'MAT-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
                    while (Material::where('code', $newCode)->exists()) {
                        $maxId++;
                        $newCode = 'MAT-'.str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
                    }
                }

                $newCategory = ! empty($data['new_category']) ? trim($data['new_category']) : ($sourceMaterial->category ?: 'Hasil Split');

                $targetMaterial = Material::create([
                    'code' => $newCode,
                    'name' => trim($data['new_name']),
                    'category' => $newCategory,
                    'unit' => $sourceMaterial->unit,
                    'stock_quantity' => 0,
                    'minimum_stock' => 0,
                    'unit_cost' => $sourceMaterial->unit_cost,
                    'last_weighed_at' => now(),
                    'last_weighed_by' => $actorBy,
                ]);
            }

            // 1. Potong stok bahan baku asal
            $sourceMaterial->decrement('stock_quantity', $sortedQty);
            $sourceMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $actorBy,
            ]);

            // 2. Tambah stok bahan baku tujuan
            $targetMaterial->increment('stock_quantity', $sortedQty);
            $targetMaterial->update([
                'last_weighed_at' => now(),
                'last_weighed_by' => $actorBy,
            ]);

            $signaturePath = null;
            if (! empty($data['signature_data']) && str_contains($data['signature_data'], 'base64,')) {
                $base64Data = explode('base64,', $data['signature_data'])[1] ?? null;
                if ($base64Data) {
                    $decodedImage = base64_decode($base64Data);
                    $filename = 'material-sorts/signatures/sig_'.time().'_'.uniqid().'.png';
                    Storage::disk('public')->put($filename, $decodedImage);
                    $signaturePath = $filename;
                }
            }

            $refNumber = 'SPL-'.date('Ymd').'-'.str_pad($sourceMaterial->id, 4, '0', STR_PAD_LEFT);

            // 3. Log Alur Barang Keluar (Split) untuk Bahan Asal
            MaterialLog::create([
                'material_id' => $sourceMaterial->id,
                'type' => 'out',
                'reference_number' => $refNumber,
                'quantity' => $sortedQty,
                'unit' => $sourceMaterial->unit,
                'actor_by' => $actorBy,
                'source_or_destination' => "Split -> [{$targetMaterial->code}] {$targetMaterial->name}",
                'movement_date' => now(),
                'notes' => $notes ?: 'Pengurangan stok karena split bahan.',
                'signature_path' => $signaturePath,
            ]);

            // 4. Log Alur Barang Masuk (Hasil Split) untuk Bahan Tujuan
            MaterialLog::create([
                'material_id' => $targetMaterial->id,
                'type' => 'in',
                'reference_number' => $refNumber,
                'quantity' => $sortedQty,
                'unit' => $targetMaterial->unit,
                'actor_by' => $actorBy,
                'source_or_destination' => "Hasil Split dari [{$sourceMaterial->code}] {$sourceMaterial->name}",
                'movement_date' => now(),
                'notes' => $notes ?: 'Penerimaan bahan baku hasil split.',
                'signature_path' => $signaturePath,
            ]);

            self::pruneOldLogs(60);

            return [
                'source' => $sourceMaterial->refresh(),
                'target' => $targetMaterial->refresh(),
                'sorted_quantity' => $sortedQty,
            ];
        });
    }

    /**
     * Hapus riwayat alur barang gudang lama agar total data log di DB tidak melebihi limit.
     */
    public static function pruneOldLogs(int $keepCount = 60): void
    {
        $idsToKeep = MaterialLog::latest('id')->take($keepCount)->pluck('id');
        if ($idsToKeep->isNotEmpty()) {
            MaterialLog::whereNotIn('id', $idsToKeep)->delete();
        }
    }
}
