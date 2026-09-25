<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionFinishingSession extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'finishing_code',
        'branch_code',
        'branch_is_merged',
        'source_branch_codes',
        'source_material_id',
        'initial_weight',
        'current_weight',
        'output_weight',
        'status',
        'product_id',
        'product_branch_id',
        'output_quantity',
        'finishing_date',
        'completed_date',
        'pic_name',
        'completion_pic_name',
        'notes',
        'completion_notes',
        'signature_path',
        'completion_signature_path',
    ];

    protected $casts = [
        'branch_is_merged' => 'boolean',
        'source_branch_codes' => 'array',
        'initial_weight' => 'decimal:2',
        'current_weight' => 'decimal:2',
        'output_weight' => 'decimal:2',
        'output_quantity' => 'integer',
        'finishing_date' => 'date',
        'completed_date' => 'date',
    ];

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'Sedang Finishing',
            self::STATUS_COMPLETED => 'Selesai',
            default => 'Status Tidak Dikenal',
        };
    }

    /**
     * Label identitas branch yang ditampilkan ke admin, sudah termasuk tanda
     * gabungan bila stok asalnya bercampur dari beberapa branch.
     */
    public function getBranchLabelAttribute(): string
    {
        if (! $this->branch_code) {
            return 'Tanpa Branch';
        }

        return $this->branch_is_merged
            ? "{$this->branch_code} (Gabungan)"
            : $this->branch_code;
    }

    public function getTotalWasteWeightAttribute(): float
    {
        return round((float) $this->steps->sum('waste_weight'), 2);
    }

    public function getTotalMaterialCostAttribute(): float
    {
        return round((float) $this->steps->sum('material_cost'), 2);
    }

    /**
     * Susut total sejak bahan masuk finishing sampai berat terkini.
     */
    public function getTotalWeightLossAttribute(): float
    {
        return round(max((float) $this->initial_weight - (float) $this->current_weight, 0), 2);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProductionFinishingStep::class, 'production_finishing_session_id')
            ->orderBy('sequence');
    }

    public function latestStep(): HasOne
    {
        return $this->hasOne(ProductionFinishingStep::class, 'production_finishing_session_id')
            ->latestOfMany('sequence');
    }

    public function sourceMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'source_material_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productBranch(): BelongsTo
    {
        return $this->belongsTo(ProductBranch::class, 'product_branch_id');
    }
}
