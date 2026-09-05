<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'type',
        'reference_number',
        'quantity',
        'unit',
        'actor_by',
        'source_or_destination',
        'movement_date',
        'notes',
        'signature_path',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
