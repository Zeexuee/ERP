<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionDailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_batch_id',
        'log_date',
        'stage',
        'progress_percentage',
        'pic_name',
        'notes',
    ];

    protected $casts = [
        'log_date' => 'date',
        'progress_percentage' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }
}
