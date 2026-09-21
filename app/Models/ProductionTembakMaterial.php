<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTembakMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_tembak_batch_id',
        'material_id',
        'type',
        'weight',
        'unit_cost',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionTembakBatch::class, 'production_tembak_batch_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
