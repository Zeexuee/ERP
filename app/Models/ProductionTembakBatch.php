<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTembakBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tembak_code',
        'wet_result_weight',
        'residual_resin_weight',
        'residual_resin_material_id',
        'dried_result_weight',
        'output_material_id',
        'status',
        'tembak_date',
        'report_date',
        'pic_name',
        'report_pic_name',
        'notes',
        'report_notes',
        'signature_path',
    ];

    protected $casts = [
        'wet_result_weight' => 'decimal:2',
        'residual_resin_weight' => 'decimal:2',
        'dried_result_weight' => 'decimal:2',
        'tembak_date' => 'date',
        'report_date' => 'date',
    ];

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->isCompleted() ? 'Selesai' : 'Sedang Ditembak';
    }

    public function materials()
    {
        return $this->hasMany(ProductionTembakMaterial::class, 'production_tembak_batch_id');
    }

    public function residualResinMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'residual_resin_material_id');
    }

    public function outputMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'output_material_id');
    }
}
