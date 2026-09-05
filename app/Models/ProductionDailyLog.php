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
        'work_status',
        'progress_percentage',
        'pic_name',
        'attachment_path',
        'signature_path',
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
