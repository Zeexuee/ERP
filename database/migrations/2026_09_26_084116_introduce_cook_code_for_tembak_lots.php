<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom penyimpan identitas lot pada setiap tabel yang terlibat.
     *
     * @var array<string, string>
     */
    private const CODE_COLUMNS = [
        'production_tembak_batches' => 'cook_code',
        'material_branches' => 'branch_code',
        'product_branches' => 'branch_code',
        'production_finishing_sessions' => 'branch_code',
    ];

    /**
     * Ubah identitas branch hasil tembak menjadi "kode masak" (MSK-) dan
     * pisahkan hasil tembak ke kategori gudang khusus.
     */
    public function up(): void
    {
        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->dropIndex(['branch_code']);
        });

        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->renameColumn('branch_code', 'cook_code');
        });

        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->index('cook_code');
        });

        // Berat asli tiap lot penyusun barang jadi, supaya rincian "78 kg =
        // 50 kg dari kode masak A + 28 kg dari lot B" tetap presisi.
        Schema::table('product_branches', function (Blueprint $table) {
            $table->decimal('weight', 12, 2)->nullable()->after('quantity');
        });

        $this->rewriteCodePrefix('BR-', 'MSK-');

        $this->reclassifyTembakOutputMaterials('Hasil Tembak');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->rewriteCodePrefix('MSK-', 'BR-');

        $this->reclassifyTembakOutputMaterials('Bahan Tembak');

        Schema::table('product_branches', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->dropIndex(['cook_code']);
        });

        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->renameColumn('cook_code', 'branch_code');
        });

        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->index('branch_code');
        });
    }

    /**
     * Tukar prefix kode lot di seluruh tabel terkait. Dikerjakan di sisi PHP
     * agar tetap aman pada MySQL maupun SQLite.
     */
    private function rewriteCodePrefix(string $from, string $to): void
    {
        foreach (self::CODE_COLUMNS as $table => $column) {
            DB::table($table)
                ->where($column, 'like', $from.'%')
                ->orderBy('id')
                ->get(['id', $column])
                ->each(function ($row) use ($table, $column, $from, $to) {
                    DB::table($table)->where('id', $row->id)->update([
                        $column => $to.substr((string) $row->{$column}, strlen($from)),
                    ]);
                });
        }

        DB::table('production_finishing_sessions')
            ->whereNotNull('source_branch_codes')
            ->orderBy('id')
            ->get(['id', 'source_branch_codes'])
            ->each(function ($row) use ($from, $to) {
                $codes = json_decode((string) $row->source_branch_codes, true);

                if (! is_array($codes)) {
                    return;
                }

                $rewritten = array_map(
                    fn ($code): string => str_starts_with((string) $code, $from)
                        ? $to.substr((string) $code, strlen($from))
                        : (string) $code,
                    $codes
                );

                DB::table('production_finishing_sessions')->where('id', $row->id)->update([
                    'source_branch_codes' => json_encode($rewritten),
                ]);
            });
    }

    /**
     * Pindahkan barang gudang hasil proses tembak ke kategori tujuan.
     */
    private function reclassifyTembakOutputMaterials(string $category): void
    {
        $outputMaterialIds = DB::table('production_tembak_batches')
            ->whereNotNull('output_material_id')
            ->distinct()
            ->pluck('output_material_id')
            ->all();

        if ($outputMaterialIds === []) {
            return;
        }

        DB::table('materials')->whereIn('id', $outputMaterialIds)->update(['category' => $category]);
    }
};
