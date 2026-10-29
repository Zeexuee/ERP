<?php

namespace App\Enums;

enum WarehouseLoanStatus: string
{
    case BORROWED = 'borrowed';
    case PARTIAL = 'partial';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::BORROWED => 'Sedang Dipinjam',
            self::PARTIAL => 'Sebagian Kembali',
            self::RETURNED => 'Sudah Kembali',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BORROWED => 'bg-amber-100 text-amber-800',
            self::PARTIAL => 'bg-sky-100 text-sky-800',
            self::RETURNED => 'bg-emerald-100 text-emerald-800',
        };
    }
}
