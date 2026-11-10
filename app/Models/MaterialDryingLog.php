<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialDryingLog extends Model
{
    protected $fillable = [
        'material_id',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'drying_hours',
        'previous_weight',
        'new_weight',
        'shrinkage_weight',
        'pic_name',
        'notes',
        'signature_path',
        'report_signature_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'drying_hours' => 'decimal:2',
        'previous_weight' => 'decimal:2',
        'new_weight' => 'decimal:2',
        'shrinkage_weight' => 'decimal:2',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
