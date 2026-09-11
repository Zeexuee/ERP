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
        'process_step',
        'work_status',
        'progress_percentage',
        'residual_resin_weight',
        'weighed_result_weight',
        'pic_name',
        'attachment_path',
        'signature_path',
        'notes',
    ];

    protected $casts = [
        'log_date' => 'date',
        'progress_percentage' => 'integer',
        'residual_resin_weight' => 'decimal:2',
        'weighed_result_weight' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function getAttachmentPathsAttribute(): array
    {
        if (empty($this->attachment_path)) {
            return [];
        }
        if (is_array($this->attachment_path)) {
            return $this->attachment_path;
        }
        $decoded = json_decode($this->attachment_path, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [$this->attachment_path];
    }
}
