<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Beri identitas branch pada stok yang sudah ada sebelum fitur branch
     * diterapkan, supaya total di gudang selalu bisa dirinci per branch.
     */
    public function up(): void
    {
        $materials = DB::table('materials')
            ->select('id', 'code', 'stock_quantity', 'unit_cost')
            ->where('stock_quantity', '>', 0)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('material_branches')
                    ->whereColumn('material_branches.material_id', 'materials.id');
            })
            ->orderBy('id')
            ->get();

        if ($materials->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $materials->map(fn ($material): array => [
            'material_id' => $material->id,
            'branch_code' => 'BRL-'.$material->code,
            'quantity' => $material->stock_quantity,
            'source_type' => 'legacy',
            'source_reference' => null,
            'notes' => 'Stok awal gudang sebelum identitas branch diterapkan.',
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('material_branches')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('material_branches')->where('source_type', 'legacy')->delete();
    }
};
