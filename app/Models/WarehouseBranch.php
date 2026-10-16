<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseBranch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'manager_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(WarehouseStockMovement::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(WarehouseStockAdjustment::class);
    }

    public function activeLocations(): HasMany
    {
        return $this->locations()->where('is_active', true);
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }

    public function lowStockItems(): HasMany
    {
        return $this->items()
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('is_active', true);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items()->where('is_active', true)->count();
    }

    public function getTotalStockAttribute(): int
    {
        return $this->items()->where('is_active', true)->sum('current_stock');
    }
}
