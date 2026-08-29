<?php

namespace App\Models;

use App\Enums\ProductionRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'request_number',
        'status',
        'requested_date',
        'completed_date',
    ];

    protected $casts = [
        'status' => ProductionRequestStatus::class,
        'requested_date' => 'datetime',
        'completed_date' => 'datetime',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
