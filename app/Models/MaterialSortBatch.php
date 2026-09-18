<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialSortBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'sort_code',
        'status',
        'source_material_id',
        'initial_weight',
        'dried_weight',
        'drying_loss_weight',
        'sort_date',
        'report_date',
        'pic_name',
        'report_pic_name',
        'notes',
        'signature_path',
        'report_signature_path',
    ];

    protected $casts = [
        'initial_weight' => 'decimal:2',
        'dried_weight' => 'decimal:2',
        'drying_loss_weight' => 'decimal:2',
        'sort_date' => 'date',
        'report_date' => 'date',
    ];

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->isCompleted() ? 'Selesai' : 'Sedang Disortir';
    }

    public function sourceMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'source_material_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialSortItem::class);
    }
}
