<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseLoanReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_loan_return_id',
        'warehouse_loan_item_id',
        'warehouse_item_id',
        'quantity_returned',
        'condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_returned' => 'integer',
        ];
    }

    public function loanReturn(): BelongsTo
    {
        return $this->belongsTo(WarehouseLoanReturn::class, 'warehouse_loan_return_id');
    }

    public function loanItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseLoanItem::class, 'warehouse_loan_item_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id');
    }
}
