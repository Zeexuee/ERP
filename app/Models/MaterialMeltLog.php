<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialMeltLog extends Model
{
    protected $fillable = [
        'material_melt_batch_id',
        'weighed_date',
        'previous_weight',
        'new_weight',
        'evaporated_weight',
        'pic_name',
        'notes',
    ];

    protected $casts = [
        'weighed_date' => 'date',
        'previous_weight' => 'decimal:2',
        'new_weight' => 'decimal:2',
        'evaporated_weight' => 'decimal:2',
    ];

    public function batch()
    {
        return $this->belongsTo(MaterialMeltBatch::class, 'material_melt_batch_id');
    }
}
