<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionTembakBatch extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DRYING = 'drying';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'tembak_code',
        'branch_code',
        'wet_result_weight',
        'residual_resin_weight',
        'residual_resin_material_id',
        'dried_result_weight',
        'output_material_id',
        'status',
        'tembak_date',
        'report_date',
        'pic_name',
        'report_pic_name',
        'notes',
        'report_notes',
        'signature_path',
        'report_signature_path',
    ];

    protected $casts = [
        'wet_result_weight' => 'decimal:2',
        'residual_resin_weight' => 'decimal:2',
        'dried_result_weight' => 'decimal:2',
        'tembak_date' => 'date',
        'report_date' => 'date',
    ];

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isDrying(): bool
    {
        return $this->status === self::STATUS_DRYING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'Sedang Ditembak',
            self::STATUS_DRYING => 'Jemur',
            self::STATUS_COMPLETED => 'Selesai',
            default => 'Status Tidak Dikenal',
        };
    }

    public function getCurrentDryingWeightAttribute(): float
    {
        if ($this->relationLoaded('dryingLogs')) {
            $latestLog = $this->dryingLogs->last();
        } elseif ($this->relationLoaded('latestDryingLog')) {
            $latestLog = $this->latestDryingLog;
        } else {
            $latestLog = $this->latestDryingLog()->first();
        }

        return (float) ($latestLog?->new_weight ?? $this->dried_result_weight ?? $this->wet_result_weight ?? 0);
    }

    public function getTotalShrinkageWeightAttribute(): float
    {
        return max(round((float) $this->wet_result_weight - $this->current_drying_weight, 2), 0);
    }

    public function getShrinkagePercentageAttribute(): float
    {
        $initialWeight = (float) $this->wet_result_weight;

        if ($initialWeight <= 0) {
            return 0;
        }

        return round(($this->total_shrinkage_weight / $initialWeight) * 100, 2);
    }

    public function getDryingDurationDaysAttribute(): int
    {
        if (! $this->report_date) {
            return 0;
        }

        if ($this->relationLoaded('dryingLogs')) {
            $latestLog = $this->dryingLogs->last();
        } elseif ($this->relationLoaded('latestDryingLog')) {
            $latestLog = $this->latestDryingLog;
        } else {
            $latestLog = $this->latestDryingLog()->first();
        }

        if ($latestLog) {
            return (int) $this->report_date->diffInDays($latestLog->weighed_date);
        }

        return $this->isDrying()
            ? (int) $this->report_date->diffInDays(now()->startOfDay())
            : 0;
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionTembakMaterial::class, 'production_tembak_batch_id');
    }

    public function dryingLogs(): HasMany
    {
        return $this->hasMany(ProductionTembakDryingLog::class, 'production_tembak_batch_id')
            ->orderBy('weighed_date')
            ->orderBy('id');
    }

    public function latestDryingLog(): HasOne
    {
        return $this->hasOne(ProductionTembakDryingLog::class, 'production_tembak_batch_id')->latestOfMany();
    }

    public function residualResinMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'residual_resin_material_id');
    }

    public function outputMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'output_material_id');
    }
}
