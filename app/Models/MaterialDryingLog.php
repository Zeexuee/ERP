<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialDryingLog extends Model
{
    protected $fillable = [
        'material_id',
        'start_date',
        'end_date',
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
        'previous_weight' => 'decimal:2',
        'new_weight' => 'decimal:2',
        'shrinkage_weight' => 'decimal:2',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
