<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'product_id',
        'production_request_id',
        'target_quantity',
        'actual_quantity',
        'unit',
        'status',
        'stage',
        'finishing_type',
        'wet_result_weight',
        'residual_resin_weight',
        'dried_result_weight',
        'start_date',
        'target_completion_date',
        'completed_date',
        'pic_name',
        'vendor_name',
        'vendor_sent_date',
        'vendor_received_date',
        'notes',
    ];

    protected $casts = [
        'target_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'wet_result_weight' => 'decimal:2',
        'residual_resin_weight' => 'decimal:2',
        'dried_result_weight' => 'decimal:2',
        'start_date' => 'date',
        'target_completion_date' => 'date',
        'completed_date' => 'date',
        'vendor_sent_date' => 'date',
        'vendor_received_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productionRequest(): BelongsTo
    {
        return $this->belongsTo(ProductionRequest::class);
    }

    public function batchMaterials(): HasMany
    {
        return $this->hasMany(ProductionBatchMaterial::class);
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(ProductionDailyLog::class)->latest('log_date');
    }
}
