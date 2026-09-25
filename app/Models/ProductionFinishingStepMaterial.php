<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionFinishingStepMaterial extends Model
{
    protected $fillable = [
        'production_finishing_step_id',
        'material_id',
        'quantity',
        'unit',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProductionFinishingStep::class, 'production_finishing_step_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function getTotalCostAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_cost, 2);
    }
}
