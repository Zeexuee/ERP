<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialMeltBatch extends Model
{
    protected $fillable = [
        'melt_code',
        'source_material_id',
        'initial_weight',
        'target_material_id',
        'initial_liquid_weight',
        'current_weight',
        'status',
        'melt_date',
        'pic_name',
        'notes',
        'report_date',
        'report_pic_name',
    ];

    protected $casts = [
        'initial_weight' => 'decimal:2',
        'initial_liquid_weight' => 'decimal:2',
        'current_weight' => 'decimal:2',
        'melt_date' => 'date',
        'report_date' => 'date',
    ];

    public function sourceMaterial()
    {
        return $this->belongsTo(Material::class, 'source_material_id');
    }

    public function targetMaterial()
    {
        return $this->belongsTo(Material::class, 'target_material_id');
    }

    public function logs()
    {
        return $this->hasMany(MaterialMeltLog::class, 'material_melt_batch_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Berat cairan saat ini = diambil langsung dari stok gudang (single source of truth).
     */
    public function getTotalEvaporatedWeightAttribute()
    {
        if (! $this->initial_liquid_weight) {
            return 0;
        }
        $currentStock = $this->targetMaterial?->stock_quantity ?? $this->current_weight ?? 0;

        return max(0, (float) $this->initial_liquid_weight - (float) $currentStock);
    }

    public function getEvaporationPercentageAttribute()
    {
        if (! $this->initial_liquid_weight || (float) $this->initial_liquid_weight <= 0) {
            return 0;
        }

        return ($this->total_evaporated_weight / (float) $this->initial_liquid_weight) * 100;
    }

    /**
     * Stok cairan saat ini langsung dari gudang.
     */
    public function getCurrentStockAttribute(): float
    {
        return (float) ($this->targetMaterial?->stock_quantity ?? $this->current_weight ?? 0);
    }
}
