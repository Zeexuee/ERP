<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLoanReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_loan_id',
        'return_number',
        'return_date',
        'receiver_name',
        'signature_path',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
        ];
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'KEMBALI-'.now()->format('Ymd');
        $latest = self::where('return_number', 'like', "{$prefix}-%")->latest('id')->first();

        if ($latest) {
            $parts = explode('-', $latest->return_number);
            $seq = (int) end($parts);
            $nextSeq = str_pad((string) ($seq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "{$prefix}-{$nextSeq}";
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(WarehouseLoan::class, 'warehouse_loan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseLoanReturnItem::class, 'warehouse_loan_return_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function totalQuantityReturned(): int
    {
        return (int) $this->items->sum('quantity_returned');
    }
}
