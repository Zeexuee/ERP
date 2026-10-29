<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLoanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_loan_id',
        'warehouse_item_id',
        'quantity_loaned',
        'quantity_returned',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_loaned' => 'integer',
            'quantity_returned' => 'integer',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(WarehouseLoan::class, 'warehouse_loan_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id');
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(WarehouseLoanReturnItem::class, 'warehouse_loan_item_id');
    }

    public function remainingQuantity(): int
    {
        return max(0, $this->quantity_loaned - $this->quantity_returned);
    }

    public function isFullyReturned(): bool
    {
        return $this->quantity_returned >= $this->quantity_loaned;
    }
}
