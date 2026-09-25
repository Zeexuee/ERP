<?php

namespace App\Services\Production;

use App\Models\Material;
use App\Models\MaterialBranch;
use App\Models\ProductionFinishingSession;
use App\Models\ProductionTembakBatch;
use Illuminate\Support\Str;

class MaterialBranchService
{
    /**
     * Prefix branch yang lahir dari proses tembak.
     */
    public const PREFIX_TEMBAK = 'BR';

    /**
     * Prefix branch gabungan yang lahir saat beberapa branch tercampur.
     */
    public const PREFIX_MERGED = 'BRM';

    /**
     * Terbitkan identitas branch baru untuk sebuah sesi tembak. Nomor urutnya
     * diambil dari tabel tembak sendiri sehingga tidak pernah bertabrakan
     * dengan branch gabungan yang memakai prefix berbeda.
     */
    public function generateTembakBranchCode(): string
    {
        return $this->nextCode(
            self::PREFIX_TEMBAK,
            fn (string $prefix): ?string => ProductionTembakBatch::query()
                ->where('branch_code', 'like', $prefix.'%')
                ->orderByDesc('branch_code')
                ->lockForUpdate()
                ->value('branch_code'),
            fn (string $code): bool => ProductionTembakBatch::where('branch_code', $code)->exists()
        );
    }

    /**
     * Terbitkan identitas branch gabungan untuk sesi finishing yang bahannya
     * berasal dari beberapa branch sekaligus.
     */
    public function generateMergedBranchCode(): string
    {
        return $this->nextCode(
            self::PREFIX_MERGED,
            fn (string $prefix): ?string => ProductionFinishingSession::query()
                ->where('branch_code', 'like', $prefix.'%')
                ->orderByDesc('branch_code')
                ->lockForUpdate()
                ->value('branch_code'),
            fn (string $code): bool => ProductionFinishingSession::where('branch_code', $code)->exists()
        );
    }

    /**
     * @param  callable(string): ?string  $latestCodeResolver
     * @param  callable(string): bool  $existsResolver
     */
    private function nextCode(string $prefix, callable $latestCodeResolver, callable $existsResolver): string
    {
        $codePrefix = $prefix.'-'.now()->format('Ym').'-';
        $latestCode = $latestCodeResolver($codePrefix);
        $sequence = $latestCode ? (int) Str::afterLast($latestCode, '-') + 1 : 1;
        $code = $codePrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        while ($existsResolver($code)) {
            $sequence++;
            $code = $codePrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * Catat masuknya stok sebuah branch ke barang gudang. Bila barang tersebut
     * sudah memuat branch lain, barang otomatis menjadi barang gabungan.
     */
    public function recordStockIn(
        Material $material,
        ?string $branchCode,
        float $quantity,
        string $sourceType,
        ?string $sourceReference = null,
        ?string $notes = null
    ): ?MaterialBranch {
        if (! $branchCode || $quantity <= 0) {
            return null;
        }

        $branch = MaterialBranch::query()
            ->where('material_id', $material->id)
            ->where('branch_code', $branchCode)
            ->lockForUpdate()
            ->first();

        if ($branch) {
            $branch->update([
                'quantity' => round((float) $branch->quantity + $quantity, 2),
                'source_reference' => $sourceReference ?? $branch->source_reference,
            ]);

            return $branch;
        }

        return MaterialBranch::create([
            'material_id' => $material->id,
            'branch_code' => $branchCode,
            'quantity' => round($quantity, 2),
            'source_type' => $sourceType,
            'source_reference' => $sourceReference,
            'notes' => $notes,
        ]);
    }

    /**
     * Kurangi stok branch pada sebuah barang secara proporsional terhadap
     * kontribusi masing-masing branch, lalu laporkan branch yang terpakai.
     *
     * @return array<string, float> Peta kode branch => berat yang terpakai.
     */
    public function consumeProportionally(Material $material, float $quantity): array
    {
        if ($quantity <= 0) {
            return [];
        }

        $branches = MaterialBranch::query()
            ->where('material_id', $material->id)
            ->where('quantity', '>', 0)
            ->orderByDesc('quantity')
            ->lockForUpdate()
            ->get();
        $totalTracked = round((float) $branches->sum('quantity'), 2);

        if ($branches->isEmpty() || $totalTracked <= 0) {
            return [];
        }

        $consumable = min($quantity, $totalTracked);
        $consumed = [];
        $allocated = 0.0;
        $lastIndex = $branches->count() - 1;

        foreach ($branches as $index => $branch) {
            // Branch terakhir menyerap sisa pembulatan agar total tetap presisi.
            $share = $index === $lastIndex
                ? round($consumable - $allocated, 2)
                : round($consumable * ((float) $branch->quantity / $totalTracked), 2);
            $share = min(max($share, 0), (float) $branch->quantity);

            if ($share <= 0) {
                continue;
            }

            $branch->update(['quantity' => round((float) $branch->quantity - $share, 2)]);
            $consumed[$branch->branch_code] = $share;
            $allocated = round($allocated + $share, 2);
        }

        return $consumed;
    }

    /**
     * Tentukan identitas branch yang dibawa sebuah proses lanjutan. Bila stok
     * asalnya bercampur, terbitkan branch gabungan baru yang menyimpan jejak
     * seluruh branch penyusunnya.
     *
     * @param  array<string, float>  $consumedBranches
     * @return array{branch_code: string|null, branch_is_merged: bool, source_branch_codes: array<int, string>|null}
     */
    public function resolveCarriedBranch(array $consumedBranches): array
    {
        $branchCodes = array_keys($consumedBranches);

        if ($branchCodes === []) {
            return [
                'branch_code' => null,
                'branch_is_merged' => false,
                'source_branch_codes' => null,
            ];
        }

        if (count($branchCodes) === 1) {
            return [
                'branch_code' => $branchCodes[0],
                'branch_is_merged' => false,
                'source_branch_codes' => $branchCodes,
            ];
        }

        return [
            'branch_code' => $this->generateMergedBranchCode(),
            'branch_is_merged' => true,
            'source_branch_codes' => $branchCodes,
        ];
    }
}
