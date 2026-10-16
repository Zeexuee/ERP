<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionColoringBatch extends Model
{
    protected $fillable = [
        'batch_code',
        'process_type',
        'coloring_date',
        'pic_name',
        'notes',
        'material_branch_id',
        'total_weight',
        'result_weight',
        'loss_weight',
        'status',
        'report_date',
        'report_pic_name',
        'report_notes',
        'signature_path',
        'report_signature_path',
        'report_image_path',
    ];

    protected $casts = [
        'coloring_date' => 'date',
        'report_date' => 'date',
        'total_weight' => 'decimal:2',
        'result_weight' => 'decimal:2',
        'loss_weight' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(MaterialBranch::class, 'material_branch_id');
    }

    public function materials()
    {
        return $this->hasMany(ProductionColoringMaterial::class, 'production_coloring_batch_id');
    }
}
