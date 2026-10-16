<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionColoringMaterial extends Model
{
    protected $fillable = [
        'production_coloring_batch_id',
        'material_id',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionColoringBatch::class, 'production_coloring_batch_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
