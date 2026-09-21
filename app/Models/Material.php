<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }
}
