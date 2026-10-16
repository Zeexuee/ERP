<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_branch_id',
        'code',
        'name',
        'zone',
        'rack',
        'bin',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(WarehouseBranch::class, 'warehouse_branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(WarehouseStockMovement::class);
    }

    public function getFullLocationCodeAttribute(): string
    {
        $parts = array_filter([
            $this->zone,
            $this->rack,
            $this->bin,
        ]);

        return empty($parts) ? $this->code : implode('-', $parts);
    }

    public function getCurrentCapacityAttribute(): int
    {
        return $this->items()->where('is_active', true)->sum('current_stock');
    }

    public function getAvailableCapacityAttribute(): ?int
    {
        if (is_null($this->capacity)) {
            return null;
        }

        return max(0, $this->capacity - $this->current_capacity);
    }

    public function isAtCapacity(): bool
    {
        if (is_null($this->capacity)) {
            return false;
        }

        return $this->current_capacity >= $this->capacity;
    }
}
