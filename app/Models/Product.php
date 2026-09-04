<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
