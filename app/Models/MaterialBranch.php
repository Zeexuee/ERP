<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialBranch extends Model
{
    public const SOURCE_TEMBAK = 'tembak';

    public const SOURCE_FINISHING = 'finishing';

    /**
     * Stok yang sudah ada sebelum identitas branch diterapkan.
     */
    public const SOURCE_LEGACY = 'legacy';

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            self::SOURCE_TEMBAK => 'Tembak',
            self::SOURCE_FINISHING => 'Finishing',
            self::SOURCE_LEGACY => 'Stok Awal',
            default => 'Lainnya',
        };
    }

    /**
     * Penamaan lot sesuai asalnya, contoh "Tembak MSK-202609-0001".
     */
    public function getLotLabelAttribute(): string
    {
        return $this->source_label.' '.$this->branch_code;
    }

    /**
     * Lot ini lahir dari proses tembak sehingga kodenya adalah kode masak.
     */
    public function isCookLot(): bool
    {
        return $this->source_type === self::SOURCE_TEMBAK;
    }

    protected $fillable = [
        'material_id',
        'branch_code',
        'quantity',
        'source_type',
        'source_reference',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
