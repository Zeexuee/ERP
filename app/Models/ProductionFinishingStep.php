<?php

namespace App\Models;

use App\Enums\FinishingProcessType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionFinishingStep extends Model
{
    protected $fillable = [
        'production_finishing_session_id',
        'sequence',
        'process_type',
        'step_date',
        'weight_before',
        'weight_after',
        'waste_weight',
        'waste_material_id',
        'material_cost',
        'pic_name',
        'notes',
        'signature_path',
    ];

    protected $casts = [
        'process_type' => FinishingProcessType::class,
        'step_date' => 'date',
        'weight_before' => 'decimal:2',
        'weight_after' => 'decimal:2',
        'waste_weight' => 'decimal:2',
        'material_cost' => 'decimal:2',
        'sequence' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ProductionFinishingSession::class, 'production_finishing_session_id');
    }

    public function stepMaterials(): HasMany
    {
        return $this->hasMany(ProductionFinishingStepMaterial::class, 'production_finishing_step_id');
    }

    public function wasteMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'waste_material_id');
    }

    /**
     * Susut total pada proses ini, termasuk ampas yang berhasil diselamatkan.
     */
    public function getWeightLossAttribute(): float
    {
        return round((float) $this->weight_before - (float) $this->weight_after, 2);
    }

    /**
     * Susut yang hilang total, yaitu susut dikurangi ampas yang dicatat.
     */
    public function getUnrecoveredLossAttribute(): float
    {
        return round(max($this->weight_loss - (float) $this->waste_weight, 0), 2);
    }
}
