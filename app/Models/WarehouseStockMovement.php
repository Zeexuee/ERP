<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_item_id',
        'warehouse_branch_id',
        'warehouse_location_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'pic_name',
        'signature_path',
        'user_id',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'movement_date' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id')->withTrashed();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(WarehouseBranch::class, 'warehouse_branch_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMovementTypeLabel(): string
    {
        return match ($this->type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'transfer' => 'Transfer',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    public function getMovementDifferenceAttribute(): int
    {
        return $this->stock_after - $this->stock_before;
    }
}
