<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'price',
        'stock_quantity',
        'unit',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ProductBranch::class);
    }

    /**
     * Total stock calculated from branches if branches are registered, otherwise fallback to stock_quantity.
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('branches') && $this->branches->isNotEmpty()) {
            return (int) $this->branches->sum('quantity');
        }

        if ($this->branches()->exists()) {
            return (int) $this->branches()->sum('quantity');
        }

        return (int) $this->stock_quantity;
    }

    /**
     * Total berat asli barang jadi, dijumlahkan dari berat tiap lot penyusunnya.
     */
    public function getTotalWeightAttribute(): float
    {
        $branches = $this->relationLoaded('branches')
            ? $this->branches
            : $this->branches()->get();

        return round((float) $branches->sum('weight'), 2);
    }

    /**
     * Rincian asal stok barang jadi, contoh "50 kg dari MSK-202609-0001".
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function lotBreakdown(): Collection
    {
        $branches = $this->relationLoaded('branches')
            ? $this->branches
            : $this->branches()->get();

        return $branches
            ->filter(fn ($branch): bool => (int) $branch->quantity > 0 || (float) $branch->weight > 0)
            ->map(fn ($branch): array => [
                'code' => $branch->branch_code ?: $branch->branch_name,
                'name' => $branch->branch_name,
                'quantity' => (int) $branch->quantity,
                'weight' => round((float) $branch->weight, 2),
                'notes' => $branch->specification_notes,
            ])
            ->sortByDesc('weight')
            ->values();
    }
}
