<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Material extends Model
{
    use HasFactory;

    /**
     * Kategori utama standar bahan baku warehouse.
     */
    /**
     * Kategori khusus penampung keluaran proses tembak, dipisahkan dari bahan
     * mentah supaya hasil masakan mudah dibedakan di gudang.
     */
    public const CATEGORY_TEMBAK_RESULT = 'Hasil Tembak';

    public const DEFAULT_CATEGORIES = [
        'Bahan Sortir',
        'Hasil Sortir',
        'Bahan Tembak',
        'Bahan Potong Ukir',
        self::CATEGORY_TEMBAK_RESULT,
        'Getah',
        'Metanol',
        'Pewarna',
        'Ampas Finishing',
    ];

    public const DEFAULT_RECEIPT_CATEGORIES = [
        'KCB',
        'Getah',
        'Metanol',
        'Pewarna',
    ];

    /**
     * Dapatkan seluruh daftar kategori bahan langsung dari data riil bahan baku yang sudah diinput.
     *
     * @return array<int, string>
     */
    public static function getAllCategories(): array
    {
        return static::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter(fn ($cat) => trim((string) $cat) !== '')
            ->map(fn ($cat) => trim((string) $cat))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Dapatkan daftar kategori bawaan dan kategori riil untuk formulir input barang masuk.
     *
     * @return array<int, string>
     */
    public static function getReceiptCategories(): array
    {
        return collect(array_merge(self::DEFAULT_RECEIPT_CATEGORIES, static::getAllCategories()))
            ->unique(fn ($item) => strtolower(trim((string) $item)))
            ->values()
            ->toArray();
    }

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'stock_quantity',
        'minimum_stock',
        'unit_cost',
        'source_tembak_code',
        'last_weighed_at',
        'last_weighed_by',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'last_weighed_at' => 'datetime',
    ];

    public function receipts(): HasMany
    {
        return $this->hasMany(MaterialReceipt::class);
    }

    public function latestReceipt(): HasOne
    {
        return $this->hasOne(MaterialReceipt::class)->latestOfMany();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaterialLog::class);
    }

    public function recountLogs(): HasMany
    {
        return $this->hasMany(MaterialLog::class)->where('type', 'recount')->latest('id');
    }

    public function dryingLogs(): HasMany
    {
        return $this->hasMany(MaterialDryingLog::class)->latest('id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(MaterialBranch::class)->orderByDesc('quantity');
    }

    public function sortBatches(): HasMany
    {
        return $this->hasMany(MaterialSortBatch::class, 'source_material_id');
    }

    public function sortResultItems(): HasMany
    {
        return $this->hasMany(MaterialSortItem::class, 'target_material_id');
    }

    public function carveBatches(): HasMany
    {
        return $this->hasMany(MaterialCarveBatch::class, 'source_material_id');
    }

    public function carveResultItems(): HasMany
    {
        return $this->hasMany(MaterialCarveItem::class, 'target_material_id');
    }

    public function meltBatches(): HasMany
    {
        return $this->hasMany(MaterialMeltBatch::class, 'source_material_id');
    }

    public function finishingSessions(): HasMany
    {
        return $this->hasMany(ProductionFinishingSession::class, 'source_material_id');
    }

    public function tembakMaterials(): HasMany
    {
        return $this->hasMany(ProductionTembakMaterial::class, 'material_id');
    }

    public function coloringMaterials(): HasMany
    {
        return $this->hasMany(ProductionColoringMaterial::class, 'material_id');
    }

    /**
     * Nama proses produksi aktif yang sedang mengunci bahan baku ini, atau null jika bebas proses.
     */
    public function getActiveProcessName(): ?string
    {
        if ($this->relationLoaded('dryingLogs')) {
            if ($this->dryingLogs->contains(fn ($log) => $log->status === 'menunggu')) {
                return 'Jemur Bahan Baku';
            }
        } elseif ($this->dryingLogs()->where('status', 'menunggu')->exists()) {
            return 'Jemur Bahan Baku';
        }

        if ($this->relationLoaded('sortBatches')) {
            if ($this->sortBatches->contains(fn ($batch) => $batch->status === 'in_progress')) {
                return 'Sortir Kayu';
            }
        } elseif ($this->sortBatches()->where('status', 'in_progress')->exists()) {
            return 'Sortir Kayu';
        }

        if ($this->relationLoaded('carveBatches')) {
            if ($this->carveBatches->contains(fn ($batch) => $batch->status === 'in_progress')) {
                return 'Potong & Ukir Kayu';
            }
        } elseif ($this->carveBatches()->where('status', 'in_progress')->exists()) {
            return 'Potong & Ukir Kayu';
        }

        if ($this->relationLoaded('meltBatches')) {
            if ($this->meltBatches->contains(fn ($batch) => $batch->status === 'in_progress')) {
                return 'Masak / Lebur Getah';
            }
        } elseif ($this->meltBatches()->where('status', 'in_progress')->exists()) {
            return 'Masak / Lebur Getah';
        }

        if ($this->relationLoaded('finishingSessions')) {
            if ($this->finishingSessions->contains(fn ($session) => $session->status === 'in_progress')) {
                return 'Finishing';
            }
        } elseif ($this->finishingSessions()->where('status', 'in_progress')->exists()) {
            return 'Finishing';
        }

        if ($this->relationLoaded('tembakMaterials')) {
            if ($this->tembakMaterials->contains(fn ($tm) => in_array($tm->batch?->status, ['in_progress', 'drying'], true))) {
                return 'Tembak Kayu';
            }
        } elseif ($this->tembakMaterials()->whereHas('batch', fn ($q) => $q->whereIn('status', ['in_progress', 'drying']))->exists()) {
            return 'Tembak Kayu';
        }

        if ($this->relationLoaded('coloringMaterials')) {
            if ($this->coloringMaterials->contains(fn ($cm) => $cm->batch?->status === 'in_progress')) {
                return 'Celup & Warna';
            }
        } elseif ($this->coloringMaterials()->whereHas('batch', fn ($q) => $q->where('status', 'in_progress'))->exists()) {
            return 'Celup & Warna';
        }

        return null;
    }

    /**
     * Apakah bahan baku sedang dalam proses produksi aktif yang belum selesai.
     */
    public function isInActiveProcess(): bool
    {
        return $this->getActiveProcessName() !== null;
    }

    /**
     * Scope untuk bahan baku yang siap diinisiasi sortir:
     * - Kategori KCB
     * - Stok > 0
     * - Sedang tidak dalam proses produksi apapun
     */
    public function scopeAvailableForSort(Builder $query): Builder
    {
        return $query->where('category', 'KCB')
            ->where('stock_quantity', '>', 0)
            ->whereDoesntHave('dryingLogs', fn ($q) => $q->where('status', 'menunggu'))
            ->whereDoesntHave('sortBatches', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('carveBatches', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('meltBatches', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('finishingSessions', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('tembakMaterials', fn ($q) => $q->whereHas('batch', fn ($bq) => $bq->whereIn('status', ['in_progress', 'drying'])))
            ->whereDoesntHave('coloringMaterials', fn ($q) => $q->whereHas('batch', fn ($bq) => $bq->where('status', 'in_progress')));
    }

    /**
     * Scope untuk bahan baku yang siap diinisiasi jemur:
     * - Kategori KCB
     * - Stok > 0
     * - Sedang tidak dalam proses produksi apapun
     */
    public function scopeAvailableForDrying(Builder $query): Builder
    {
        return $query->availableForSort();
    }

    /**
     * Scope untuk bahan baku yang siap diinisiasi potong ukir:
     * - Kategori 'Bahan Potong Ukir'
     * - Stok > 0
     * - Sedang tidak dalam proses produksi apapun
     */
    public function scopeAvailableForCarve(Builder $query): Builder
    {
        return $query->where('category', 'Bahan Potong Ukir')
            ->where('stock_quantity', '>', 0)
            ->whereDoesntHave('dryingLogs', fn ($q) => $q->where('status', 'menunggu'))
            ->whereDoesntHave('sortBatches', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('carveBatches', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('meltBatches', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('finishingSessions', fn ($q) => $q->where('status', 'in_progress'))
            ->whereDoesntHave('tembakMaterials', fn ($q) => $q->whereHas('batch', fn ($bq) => $bq->whereIn('status', ['in_progress', 'drying'])))
            ->whereDoesntHave('coloringMaterials', fn ($q) => $q->whereHas('batch', fn ($bq) => $bq->where('status', 'in_progress')));
    }

    /**
     * Kumpulan riwayat seluruh proses produksi dan barang masuk yang dialami bahan baku ini.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAllProcessLogs(): Collection
    {
        $logs = collect();

        // 1. Penerimaan Barang Masuk (Barang Masuk Baru)
        $receipts = $this->relationLoaded('receipts')
            ? $this->receipts
            : $this->receipts()->latest('id')->get();

        foreach ($receipts as $receipt) {
            $date = $receipt->received_date ? Carbon::parse($receipt->received_date)->startOfDay() : ($receipt->created_at ?? now());
            $dateDisplay = $receipt->received_date ? $receipt->received_date->format('d/m/Y') : ($receipt->created_at ? $receipt->created_at->format('d/m/Y H:i') : '-');

            $logs->push([
                'type' => 'Barang Masuk',
                'badge' => 'bg-slate-900 text-white',
                'reference_number' => $receipt->receipt_number,
                'date' => $date,
                'date_display' => $dateDisplay,
                'time_details' => 'Diterima: ' . $dateDisplay,
                'weight_summary' => '+' . number_format((float) $receipt->quantity, 2) . ' ' . $this->unit,
                'weight_subtext' => $receipt->source_or_supplier ? 'Asal: ' . $receipt->source_or_supplier : null,
                'status' => 'Diterima',
                'status_badge' => 'bg-slate-100 text-slate-800 border border-slate-300',
                'pic_name' => $receipt->received_by ?: 'Warehouse',
                'notes' => $receipt->notes ?: '-',
                'action_url' => $receipt->image_path ? asset('storage/' . $receipt->image_path) : ($receipt->signature_path ? asset('storage/' . $receipt->signature_path) : null),
                'action_label' => $receipt->image_path ? 'Foto Bukti' : ($receipt->signature_path ? 'Lihat TTD' : null),
                'action_target_blank' => true,
            ]);
        }

        // 2. Jemur Bahan
        $dryingLogs = $this->relationLoaded('dryingLogs')
            ? $this->dryingLogs
            : $this->dryingLogs()->latest('id')->get();

        foreach ($dryingLogs as $dLog) {
            $date = $dLog->start_date ? Carbon::parse($dLog->start_date)->startOfDay() : ($dLog->created_at ?? now());
            $startDateStr = $dLog->start_date ? $dLog->start_date->format('d/m/Y') : '-';
            $startTimeStr = $dLog->start_time ? '(' . $dLog->start_time . ')' : '';
            $endDateStr = $dLog->end_date ? $dLog->end_date->format('d/m/Y') : null;
            $endTimeStr = $dLog->end_time ? '(' . $dLog->end_time . ')' : '';

            $timeDetails = 'Mulai: ' . $startDateStr . ' ' . $startTimeStr;
            if ($endDateStr) {
                $timeDetails .= '<br><span class="text-slate-500 text-[10px]">Selesai: ' . $endDateStr . ' ' . $endTimeStr . '</span>';
            }

            $weightSummary = number_format((float) $dLog->previous_weight, 2) . ' ' . $this->unit;
            if ($dLog->new_weight !== null) {
                $weightSummary .= ' → ' . number_format((float) $dLog->new_weight, 2) . ' ' . $this->unit;
            }

            $weightSubtext = [];
            if ((float) $dLog->shrinkage_weight > 0) {
                $weightSubtext[] = '-' . number_format((float) $dLog->shrinkage_weight, 2) . ' (Susut)';
            }
            if ($dLog->drying_hours !== null) {
                $weightSubtext[] = (float) $dLog->drying_hours . ' Jam';
            }

            $isWaiting = $dLog->status === 'menunggu';

            $logs->push([
                'type' => 'Jemur Bahan',
                'badge' => 'bg-slate-900 text-white',
                'reference_number' => 'JMR-#' . $dLog->id,
                'date' => $date,
                'date_display' => $startDateStr,
                'time_details' => $timeDetails,
                'weight_summary' => $weightSummary,
                'weight_subtext' => !empty($weightSubtext) ? implode(' · ', $weightSubtext) : null,
                'status' => $isWaiting ? 'Menjemur' : 'Selesai',
                'status_badge' => $isWaiting ? 'bg-slate-100 text-slate-800 border border-slate-300' : 'bg-slate-900 text-white',
                'pic_name' => $dLog->pic_name ?: '-',
                'notes' => $dLog->notes ?: '-',
                'action_url' => $isWaiting ? route('production.dryings.report.create', $dLog->id) : null,
                'action_label' => $isWaiting ? 'Lapor' : null,
                'action_target_blank' => false,
            ]);
        }

        // 3. Sortir Kayu (Sebagai Bahan Baku Asal)
        $sortBatches = $this->relationLoaded('sortBatches')
            ? $this->sortBatches
            : $this->sortBatches()->latest('id')->get();

        foreach ($sortBatches as $sort) {
            $date = $sort->sort_date ? Carbon::parse($sort->sort_date)->startOfDay() : ($sort->created_at ?? now());
            $startDateStr = $sort->sort_date ? $sort->sort_date->format('d/m/Y') : '-';
            $endDateStr = $sort->report_date ? $sort->report_date->format('d/m/Y') : null;

            $timeDetails = 'Mulai: ' . $startDateStr;
            if ($endDateStr) {
                $timeDetails .= '<br><span class="text-slate-500 text-[10px]">Selesai: ' . $endDateStr . '</span>';
            }

            $weightSummary = 'Awal: ' . number_format((float) $sort->initial_weight, 2) . ' ' . $this->unit;
            if ($sort->dried_weight !== null) {
                $weightSummary .= ' → Hasil: ' . number_format((float) $sort->dried_weight, 2) . ' ' . $this->unit;
            }

            $weightSubtext = [];
            if ((float) $sort->drying_loss_weight > 0) {
                $weightSubtext[] = '-' . number_format((float) $sort->drying_loss_weight, 2) . ' (Susut)';
            }
            if ((float) $sort->returned_weight > 0) {
                $weightSubtext[] = number_format((float) $sort->returned_weight, 2) . ' (Sisa)';
            }

            $isCompleted = $sort->isCompleted();

            $logs->push([
                'type' => 'Sortir Kayu',
                'badge' => 'bg-slate-800 text-white',
                'reference_number' => $sort->sort_code,
                'date' => $date,
                'date_display' => $startDateStr,
                'time_details' => $timeDetails,
                'weight_summary' => $weightSummary,
                'weight_subtext' => !empty($weightSubtext) ? implode(' · ', $weightSubtext) : null,
                'status' => $isCompleted ? 'Selesai' : 'Sedang Disortir',
                'status_badge' => $isCompleted ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-800 border border-slate-300',
                'pic_name' => $sort->report_pic_name ?: ($sort->pic_name ?: '-'),
                'notes' => $sort->notes ?: '-',
                'action_url' => route('production.sorts.show', $sort->id),
                'action_label' => $isCompleted ? 'Buka' : 'Lapor',
                'action_target_blank' => false,
            ]);
        }

        // 4. Hasil Sortir Kayu (Sebagai Bahan Target yang Dihasilkan)
        $sortResultItems = $this->relationLoaded('sortResultItems')
            ? $this->sortResultItems
            : $this->sortResultItems()->with(['sortBatch.sourceMaterial'])->latest('id')->get();

        foreach ($sortResultItems as $item) {
            $batch = $item->sortBatch;
            $date = $batch?->report_date ? Carbon::parse($batch->report_date)->startOfDay() : ($item->created_at ?? now());
            $dateDisplay = $batch?->report_date ? $batch->report_date->format('d/m/Y') : ($item->created_at ? $item->created_at->format('d/m/Y') : '-');

            $sourceName = $batch?->sourceMaterial?->name;

            $logs->push([
                'type' => 'Hasil Sortir',
                'badge' => 'bg-slate-100 text-slate-900 border border-slate-300',
                'reference_number' => $batch?->sort_code ?? '-',
                'date' => $date,
                'date_display' => $dateDisplay,
                'time_details' => 'Dihasilkan: ' . $dateDisplay,
                'weight_summary' => '+' . number_format((float) $item->result_weight, 2) . ' ' . $this->unit,
                'weight_subtext' => $sourceName ? 'Dari: ' . $sourceName : 'Hasil Sortir Kayu',
                'status' => 'Selesai',
                'status_badge' => 'bg-slate-900 text-white',
                'pic_name' => $batch?->report_pic_name ?: ($batch?->pic_name ?: '-'),
                'notes' => $item->notes ?: '-',
                'action_url' => $item->material_sort_batch_id ? route('production.sorts.show', $item->material_sort_batch_id) : null,
                'action_label' => 'Buka',
                'action_target_blank' => false,
            ]);
        }

        // 5. Potong & Ukir (Sebagai Bahan Baku Asal)
        $carveBatches = $this->relationLoaded('carveBatches')
            ? $this->carveBatches
            : $this->carveBatches()->latest('id')->get();

        foreach ($carveBatches as $carve) {
            $date = $carve->carve_date ? Carbon::parse($carve->carve_date)->startOfDay() : ($carve->created_at ?? now());
            $startDateStr = $carve->carve_date ? $carve->carve_date->format('d/m/Y') : '-';
            $endDateStr = $carve->report_date ? $carve->report_date->format('d/m/Y') : null;

            $timeDetails = 'Mulai: ' . $startDateStr;
            if ($endDateStr) {
                $timeDetails .= '<br><span class="text-slate-500 text-[10px]">Selesai: ' . $endDateStr . '</span>';
            }

            $weightSummary = 'Awal: ' . number_format((float) $carve->initial_weight, 2) . ' ' . $this->unit;
            if ($carve->dried_weight !== null) {
                $weightSummary .= ' → Hasil: ' . number_format((float) $carve->dried_weight, 2) . ' ' . $this->unit;
            }

            $weightSubtext = [];
            if ((float) $carve->drying_loss_weight > 0) {
                $weightSubtext[] = '-' . number_format((float) $carve->drying_loss_weight, 2) . ' (Susut)';
            }

            $isCompleted = $carve->isCompleted();

            $logs->push([
                'type' => 'Potong Ukir',
                'badge' => 'bg-slate-800 text-white',
                'reference_number' => $carve->carve_code,
                'date' => $date,
                'date_display' => $startDateStr,
                'time_details' => $timeDetails,
                'weight_summary' => $weightSummary,
                'weight_subtext' => !empty($weightSubtext) ? implode(' · ', $weightSubtext) : null,
                'status' => $isCompleted ? 'Selesai' : 'Sedang Dikerjakan',
                'status_badge' => $isCompleted ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-800 border border-slate-300',
                'pic_name' => $carve->report_pic_name ?: ($carve->pic_name ?: '-'),
                'notes' => $carve->notes ?: '-',
                'action_url' => route('production.carves.show', $carve->id),
                'action_label' => $isCompleted ? 'Buka' : 'Lapor',
                'action_target_blank' => false,
            ]);
        }

        // 6. Hasil Potong Ukir (Sebagai Bahan Target yang Dihasilkan)
        $carveResultItems = $this->relationLoaded('carveResultItems')
            ? $this->carveResultItems
            : $this->carveResultItems()->with(['carveBatch.sourceMaterial'])->latest('id')->get();

        foreach ($carveResultItems as $item) {
            $batch = $item->carveBatch;
            $date = $batch?->report_date ? Carbon::parse($batch->report_date)->startOfDay() : ($item->created_at ?? now());
            $dateDisplay = $batch?->report_date ? $batch->report_date->format('d/m/Y') : ($item->created_at ? $item->created_at->format('d/m/Y') : '-');

            $sourceName = $batch?->sourceMaterial?->name;

            $logs->push([
                'type' => 'Hasil Potong Ukir',
                'badge' => 'bg-slate-100 text-slate-900 border border-slate-300',
                'reference_number' => $batch?->carve_code ?? '-',
                'date' => $date,
                'date_display' => $dateDisplay,
                'time_details' => 'Dihasilkan: ' . $dateDisplay,
                'weight_summary' => '+' . number_format((float) $item->result_weight, 2) . ' ' . $this->unit,
                'weight_subtext' => $sourceName ? 'Dari: ' . $sourceName : 'Hasil Potong Ukir',
                'status' => 'Selesai',
                'status_badge' => 'bg-slate-900 text-white',
                'pic_name' => $batch?->report_pic_name ?: ($batch?->pic_name ?: '-'),
                'notes' => $item->notes ?: '-',
                'action_url' => $item->material_carve_batch_id ? route('production.carves.show', $item->material_carve_batch_id) : null,
                'action_label' => 'Buka',
                'action_target_blank' => false,
            ]);
        }

        return $logs->sortByDesc(fn ($item) => $item['date']?->timestamp ?? 0)->values();
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    /**
     * Branch yang masih menyimpan stok pada barang ini.
     *
     * @return Collection<int, MaterialBranch>
     */
    public function activeBranches(): Collection
    {
        $branches = $this->relationLoaded('branches')
            ? $this->branches
            : $this->branches()->get();

        return $branches->where('quantity', '>', 0)->values();
    }

    /**
     * Total stok yang sudah punya identitas branch.
     */
    public function getBranchedQuantityAttribute(): float
    {
        return round((float) $this->activeBranches()->sum('quantity'), 2);
    }

    /**
     * Stok yang belum punya identitas branch. Nilainya di atas 0 berarti ada
     * pemasukan stok dari alur yang belum mencatat branch.
     */
    public function getUnbranchedQuantityAttribute(): float
    {
        return round(max((float) $this->stock_quantity - $this->branched_quantity, 0), 2);
    }

    /**
     * Buku besar branch melebihi stok gudang, menandakan data perlu ditinjau.
     */
    public function hasBranchDiscrepancy(): bool
    {
        return $this->branched_quantity - (float) $this->stock_quantity > 0.001;
    }

    /**
     * Label identitas branch stok barang ini. Barang yang memuat lebih dari satu
     * branch ditandai sebagai gabungan.
     */
    public function getBranchLabelAttribute(): string
    {
        $activeBranches = $this->activeBranches();

        if ($activeBranches->isEmpty()) {
            return 'Tanpa Branch';
        }

        if ($activeBranches->count() === 1) {
            return (string) $activeBranches->first()->branch_code;
        }

        return $activeBranches->pluck('branch_code')->implode(' + ').' (Gabungan)';
    }
}
