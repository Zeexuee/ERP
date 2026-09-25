<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTembakDryingLog extends Model
{
    protected $fillable = [
        'production_tembak_batch_id',
        'weighed_date',
        'previous_weight',
        'new_weight',
        'shrinkage_weight',
        'pic_name',
        'notes',
        'is_final',
    ];

    protected $casts = [
        'weighed_date' => 'date',
        'previous_weight' => 'decimal:2',
        'new_weight' => 'decimal:2',
        'shrinkage_weight' => 'decimal:2',
        'is_final' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionTembakBatch::class, 'production_tembak_batch_id');
    }
}
