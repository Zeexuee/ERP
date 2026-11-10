<?php

namespace App\Models;

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

    public function carveBatches(): HasMany
    {
        return $this->hasMany(MaterialCarveBatch::class, 'source_material_id');
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
