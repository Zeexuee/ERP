<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialSortItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_sort_batch_id',
        'target_material_id',
        'result_weight',
        'notes',
    ];

    protected $casts = [
        'result_weight' => 'decimal:2',
    ];

    public function sortBatch(): BelongsTo
    {
        return $this->belongsTo(MaterialSortBatch::class, 'material_sort_batch_id');
    }

    public function targetMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'target_material_id');
    }
}
