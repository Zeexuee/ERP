<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_item_id',
        'warehouse_branch_id',
        'reason',
        'quantity_before',
        'quantity_after',
        'adjustment_quantity',
        'notes',
        'user_id',
        'adjustment_date',
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'adjustment_quantity' => 'integer',
        'adjustment_date' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(WarehouseBranch::class, 'warehouse_branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReasonLabel(): string
    {
        return match ($this->reason) {
            'damaged' => 'Damaged',
            'lost' => 'Lost',
            'found' => 'Found',
            'expired' => 'Expired',
            'audit' => 'Audit Correction',
            'other' => 'Other',
            default => ucfirst($this->reason),
        };
    }

    public function isPositiveAdjustment(): bool
    {
        return $this->adjustment_quantity > 0;
    }

    public function isNegativeAdjustment(): bool
    {
        return $this->adjustment_quantity < 0;
    }
}
