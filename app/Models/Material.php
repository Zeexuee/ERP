<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Material extends Model
{
    use HasFactory;

    /**
     * Kategori utama standar bahan baku warehouse.
     */
    public const DEFAULT_CATEGORIES = [
        'Bahan Sortir',
        'Bahan Tembak',
        'Getah',
        'Metanol',
        'Pewarna',
        'Ampas Finishing',
    ];

    /**
     * Dapatkan seluruh daftar kategori bahan (kategori default utama + kategori custom dari database).
     *
     * @return array<int, string>
     */
    public static function getAllCategories(): array
    {
        $existing = static::query()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->toArray();

        return collect(array_merge(self::DEFAULT_CATEGORIES, $existing))
            ->unique(fn ($item) => strtolower(trim($item)))
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

    public function batchMaterials(): HasMany
    {
        return $this->hasMany(ProductionBatchMaterial::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaterialLog::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(MaterialBranch::class)->orderByDesc('quantity');
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
