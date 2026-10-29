<?php

namespace App\Models;

use App\Enums\WarehouseLoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_number',
        'borrower_name',
        'borrower_contact',
        'purpose',
        'loan_date',
        'expected_return_date',
        'status',
        'notes',
        'signature_path',
        'user_id',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'expected_return_date' => 'date',
            'returned_at' => 'datetime',
            'status' => WarehouseLoanStatus::class,
        ];
    }

    public static function generateLoanNumber(): string
    {
        $prefix = 'PINJAM-'.now()->format('Ymd');
        $latest = self::where('loan_number', 'like', "{$prefix}-%")->latest('id')->first();

        if ($latest) {
            $parts = explode('-', $latest->loan_number);
            $seq = (int) end($parts);
            $nextSeq = str_pad((string) ($seq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "{$prefix}-{$nextSeq}";
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseLoanItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(WarehouseLoanReturn::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function totalQuantityLoaned(): int
    {
        return (int) $this->items->sum('quantity_loaned');
    }

    public function totalQuantityReturned(): int
    {
        return (int) $this->items->sum('quantity_returned');
    }

    public function totalQuantityRemaining(): int
    {
        return max(0, $this->totalQuantityLoaned() - $this->totalQuantityReturned());
    }

    public function isOverdue(): bool
    {
        if ($this->status === WarehouseLoanStatus::RETURNED) {
            return false;
        }

        if (! $this->expected_return_date) {
            return false;
        }

        return now()->startOfDay()->gt($this->expected_return_date->startOfDay());
    }

    public function refreshStatus(): void
    {
        $totalLoaned = $this->totalQuantityLoaned();
        $totalReturned = $this->totalQuantityReturned();

        if ($totalReturned >= $totalLoaned && $totalLoaned > 0) {
            $this->status = WarehouseLoanStatus::RETURNED;
            $this->returned_at = $this->returned_at ?? now();
        } elseif ($totalReturned > 0) {
            $this->status = WarehouseLoanStatus::PARTIAL;
            $this->returned_at = null;
        } else {
            $this->status = WarehouseLoanStatus::BORROWED;
            $this->returned_at = null;
        }

        $this->save();
    }
}
