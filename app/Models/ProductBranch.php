<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_name',
        'branch_code',
        'quantity',
        'weight',
        'specification_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
